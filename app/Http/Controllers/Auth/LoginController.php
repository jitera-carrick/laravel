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

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Combine validation rules and custom error messages
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha' => 'required|string', // Keep recaptcha validation
        ], [
            'email.email' => 'Invalid email address.',
            'password.required' => 'Incorrect password.',
            'recaptcha.required' => 'Invalid recaptcha.', // Keep recaptcha error message
        ]);

        if ($validator->fails()) {
            // Return the appropriate response codes and error messages as per the new requirements
            $errors = $validator->errors();
            $firstError = $errors->first();
            $statusCode = 400;
            return response()->json(['error' => $firstError], $statusCode);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email does not exist.'], 400);
        }

        // Check recaptcha validity
        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        // Retrieve password_hash and password_salt, hash provided password
        // Assuming that the User model has password_hash and password_salt fields
        // Use Hash::check for password verification
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

        // Generate new session_token and update user
        $sessionToken = Str::random(60);
        $user->forceFill([
            'session_token' => $sessionToken,
            'is_logged_in' => true,
            'updated_at' => now(),
        ])->save();

        // Return successful login response with session_token
        return response()->json([
            'status' => 200,
            'message' => 'Login successful.',
            'session_token' => $sessionToken,
        ]);
    }

    public function logout(Request $request)
    {
        // Logout method remains unchanged as there is no conflict
        try {
            $sessionToken = $request->cookie('session_token'); // Use the cookie method to retrieve the session token
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
            }

            return response()->json([
                'status' => 400,
                'message' => 'No active session found.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during logout.',
                'error' => $e->getMessage()
            ]);
        }
    }

    // ... (rest of the code remains unchanged)
}
