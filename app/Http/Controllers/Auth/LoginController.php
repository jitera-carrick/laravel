<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Session;
use App\Services\RecaptchaService; // Import the RecaptchaService
use Illuminate\Support\Facades\Log; // Import Log facade for logging exceptions

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Updated validation rules with custom error messages
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            // 'recaptcha' => 'required|string', // Recaptcha is not mentioned in the new requirement, so it's removed
        ], [
            'email.email' => 'Invalid email address.',
            'password.required' => 'Incorrect password.',
            // 'recaptcha.required' => 'Invalid recaptcha.', // Recaptcha error message removed
        ]);

        if ($validator->fails()) {
            // Return the appropriate response codes and error messages as per the new requirements
            $errors = $validator->errors();
            $firstError = $errors->first();
            $statusCode = 400;
            // Recaptcha error handling removed
            return response()->json(['error' => $firstError], $statusCode);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email does not exist.'], 400);
        }

        // Check if recaptcha is present in the request and verify it
        if ($request->has('recaptcha') && !RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        // Retrieve password_hash and password_salt, hash provided password
        // Assuming that the hashing mechanism uses the 'salt' option
        $hashedPassword = Hash::make($password, ['salt' => $user->password_salt]);

        // Compare hashed password with password_hash
        if (!Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 401);
        }

        // Record successful login attempt
        LoginAttempt::create([
            'user_id' => $user->id,
            'attempted_at' => now(),
            'successful' => true,
            'ip_address' => $request->ip(),
        ]);

        if ($user->email_verified_at !== null) {
            // Generate new session_token and update user
            $sessionToken = Str::random(60);
            $user->forceFill([
                'session_token' => $sessionToken,
                'is_logged_in' => true,
                'updated_at' => now(),
            ])->save();

            // Return successful login response
            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'session_token' => $sessionToken,
            ]);
        } else {
            // Return error response for unverified email
            return response()->json(['error' => 'Email has not been verified.'], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Retrieve the "session_token" from the request body or cookie
            $sessionToken = $request->input('session_token') ?: $request->cookie('session_token');

            // Query the "users" table to find the user with the matching "session_token"
            $user = User::where('session_token', $sessionToken)->first();

            if ($user) {
                // Set the "session_token" to null and "is_logged_in" to false in the "users" table
                $user->session_token = null;
                $user->is_logged_in = false;
                $user->save(); // Save the changes to the user model

                // Return a success response indicating the user has been successfully logged out
                return response()->json([
                    'status' => 200,
                    'message' => 'Logout successful.'
                ]);
            }

            // If no user is found with the provided session token, return an error response
            return response()->json([
                'status' => 400,
                'message' => 'Invalid session token.'
            ]);
        } catch (\Exception $e) {
            // Log the exception for debugging purposes
            Log::error('Logout error: ' . $e->getMessage());

            // Return an appropriate error message
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during logout.',
                'error' => $e->getMessage()
            ]);
        }
    }

    // ... (rest of the code remains unchanged)
}
