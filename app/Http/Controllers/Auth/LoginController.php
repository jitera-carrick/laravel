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

        if (!Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 401);
        }

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

        if ($user->email_verified_at !== null) {
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
        } else {
            // Return error response for unverified email
            return response()->json(['error' => 'Email has not been verified.'], 401);
        }
    }

    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $sessionToken = $request->input('session_token'); // Retrieve the session token from the request body

        // Attempt to retrieve the session using the session token from the request body first
        $session = Session::where('session_token', $sessionToken)
                          ->where('is_active', true)
                          ->first();

        if ($session) {
            $user = $session->user;
            $user->forceFill([
                'is_logged_in' => false,
                'remember_token' => null, // Clear the session token
            ])->save();

            $session->forceFill([
                'is_active' => false,
            ])->save();

            Cookie::queue(Cookie::forget('session_token'));

            return response()->json([
                'status' => 200,
                'message' => 'Logout successful.'
            ]);
        } else {
            // If no session was found using the session token from the request body, attempt to retrieve it from the cookie
            $sessionToken = $request->cookie('session_token');
            $user = User::where('remember_token', $sessionToken)->first();

            if ($user) {
                $user->forceFill([
                    'is_logged_in' => false,
                    'remember_token' => null, // Clear the session token
                ])->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Logout successful.'
                ]);
            }

            return response()->json([
                'status' => 404,
                'message' => 'User not found or session token invalid.'
            ]);
        }
    }
}
