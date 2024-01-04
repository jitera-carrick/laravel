<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth; // Added Auth facade
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Session;
use App\Services\RecaptchaService; // Import the RecaptchaService

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email does not exist.'], 400);
        }

        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        // Retrieve the user's password_hash and password_salt from the database
        $passwordHash = $user->password_hash ?? $user->password; // Fallback to password if password_hash is not set
        $passwordSalt = $user->password_salt ?? null; // Fallback to null if password_salt is not set

        // Hash the provided password with the retrieved password_salt if it exists
        $hashedPassword = $passwordSalt ? Hash::make($password, ['salt' => $passwordSalt]) : $password;

        // Compare the hashed password with the password_hash in the database
        if (Hash::check($hashedPassword, $passwordHash)) {
            // Record successful login attempt
            LoginAttempt::create([
                'user_id' => $user->id,
                'attempted_at' => now(),
                'successful' => true,
                'ip_address' => $request->ip(),
            ]);

            // Generate a new session_token
            $sessionToken = Str::random(60);

            // Update the user's session_token, session_expiration, and set is_logged_in to true
            $user->forceFill([
                'session_token' => $sessionToken,
                'session_expiration' => now()->addMinutes(120), // Assuming session expires after 120 minutes
                'is_logged_in' => true,
                'updated_at' => now(),
            ])->save();

            // Return a success response with the session_token
            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'session_token' => $sessionToken,
            ]);
        } else {
            // Return an error response if the hashes do not match
            return response()->json(['error' => 'Incorrect password.'], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Retrieve the "session_token" from the request header, body, or cookie
            $sessionToken = $request->header('session_token') ?? $request->input('session_token') ?? $request->cookie('session_token');
            // Find the session with the matching "session_token"
            $session = Session::where('session_token', $sessionToken)
                              ->where('is_active', true)
                              ->first();

            if ($session) {
                $user = $session->user;
                $user->is_logged_in = false;
                $user->save();

                $session->is_active = false;
                $session->save();

                Cookie::queue(Cookie::forget('session_token'));

                return response()->json([
                    'status' => 200,
                    'message' => 'Logout successful.'
                ]);
            } else {
                // Find the user with the matching "session_token"
                $user = User::where('session_token', $sessionToken)->first();

                if ($user) {
                    // Clear the "session_token" field, set "is_logged_in" to false, and update the "session_expiration"
                    $user->update([
                        'session_token' => null,
                        'is_logged_in' => false, // Set "is_logged_in" to false
                        'session_expiration' => now(), // Update "session_expiration" to the current datetime
                    ]);
                }

                return response()->json([
                    'status' => 400,
                    'message' => 'No active session found.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during logout.',
                'error' => $e->getMessage()
            ]);
        }
    }
}
