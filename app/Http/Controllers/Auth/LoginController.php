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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha' => 'required|string', // Keep the recaptcha validation
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

        if (!Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 401);
        }
        
        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        // Record successful login attempt
        LoginAttempt::create([
            'user_id' => $user->id,
            'attempted_at' => now(), // Keep the login attempt recording
            'successful' => true,
            'ip_address' => $request->ip(),
        ]);

        if ($user->email_verified_at !== null) {
            // Generate new remember_token and update user
            $user->forceFill([
                'remember_token' => Str::random(60), // Keep the remember_token generation
                'updated_at' => now(),
            ])->save();

            // Return successful login response
            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'token' => $user->remember_token,
            ]); // Keep the successful login response
        } else {
            // Return error response for unverified email
            return response()->json(['error' => 'Email has not been verified.'], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            $sessionToken = $request->cookie('session_token'); // Use the cookie method to retrieve the session token
            if (!$sessionToken) {
                $sessionToken = $request->header('session_token'); // Attempt to retrieve the session token from the header
                if (!$sessionToken) {
                    $sessionToken = $request->input('session_token'); // Fallback to the request body if header is not set
                }
            }
            
            $session = Session::where('session_token', $sessionToken)
                              ->where('is_active', true)
                              ->first();

            if ($session) {
                $user = $session->user; // Keep the user retrieval from session
                $user->forceFill([
                    'session_token' => null,
                    'is_logged_in' => false,
                    'session_expiration' => now(),
                ])->save();

                $session->is_active = false;
                $session->save();

                Cookie::queue(Cookie::forget('session_token')); // Keep the cookie forgetting logic

                return response()->json([
                    'status' => 200,
                    'message' => 'Logout successful.'
                ]);
            }

            return response()->json([ // Keep the no active session response
                'status' => 400,
                'message' => 'No active session found.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during logout.', // Keep the error handling during logout
                'error' => $e->getMessage()
            ]);
        }
    }
}
