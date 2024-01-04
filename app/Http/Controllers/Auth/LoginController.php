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

        // Check if the user exists and verify the password
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email does not exist.'], 400);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 401);
        }

        // Verify the recaptcha
        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        // Record successful login attempt
        LoginAttempt::create([
            'user_id' => $user->id,
            'attempted_at' => now(),
            'successful' => true,
            'ip_address' => $request->ip(),
        ]);

        // Check if the email is verified
        if ($user->email_verified_at !== null) {
            // Attempt to authenticate the user using Auth facade
            if (Auth::attempt(['email' => $email, 'password' => $password])) {
                $user = Auth::user();
                $token = Str::random(60);
                $user->forceFill([
                    'session_token' => $token,
                    'is_logged_in' => true,
                    'session_expiration' => now()->addMinutes(config('session.lifetime')),
                    'updated_at' => now(),
                ])->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Login successful.',
                    'token' => $token,
                ]);
            } else {
                // Generate new remember_token and update user
                $user->forceFill([
                    'remember_token' => Str::random(60),
                    'updated_at' => now(),
                ])->save();

                // Return successful login response
                return response()->json([
                    'status' => 200,
                    'message' => 'Login successful.',
                    'token' => $user->remember_token,
                ]);
            }
        } else {
            // Return error response for unverified email
            return response()->json(['error' => 'Email has not been verified.'], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Attempt to retrieve the session token from the request cookie first
            $sessionToken = $request->cookie('session_token');
            if (!$sessionToken) {
                // If not found in the cookie, attempt to retrieve it from the request body
                $sessionToken = $request->input('session_token');
            }

            // Query the "sessions" table using the Eloquent model `Session` to find a record with the matching "session_token"
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
                // Query the "users" table using the Eloquent model `User` to find a record with the matching "session_token"
                $user = User::where('session_token', $sessionToken)->first();

                if ($user) {
                    // Update the "is_logged_in" field to false and clear the "session_token" and "session_expiration" fields in the user's record
                    $user->is_logged_in = false;
                    $user->session_token = null;
                    $user->session_expiration = null;
                    $user->save();

                    // Return a success response indicating the user has been logged out
                    return response()->json([
                        'status' => 200,
                        'message' => 'Logout successful.'
                    ]);
                }
            }

            // If no matching session token is found, return an error response indicating the logout failed
            return response()->json([
                'status' => 400,
                'message' => 'Logout failed. No matching session token found.'
            ]);

        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the logout process and return an appropriate error response
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during logout.',
                'error' => $e->getMessage()
            ]);
        }
    }
}
