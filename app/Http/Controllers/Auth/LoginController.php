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
use Illuminate\Support\Facades\Auth; // Added Auth facade
use App\Models\Session;
use App\Services\RecaptchaService; // Import the RecaptchaService
use App\Services\AuthService; // Import the AuthService

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Validate recaptcha first
        $recaptchaValidator = Validator::make($request->all(), [
            'recaptcha' => 'required|string',
        ]);

        if ($recaptchaValidator->fails()) {
            return response()->json($recaptchaValidator->errors(), 422);
        }

        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        // Validate email and password
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $response = [];
            if ($errors->has('email')) {
                $response['email'] = $errors->first('email');
            }
            if ($errors->has('password')) {
                $response['password'] = $errors->first('password');
            }
            return response()->json(['message' => $response], 400);
        }

        // Attempt to authenticate the user
        $credentials = $request->only('email', 'password');
        $keepSession = $request->input('keep_session', false);
        $user = AuthService::attemptLogin($credentials, $keepSession);

        if (!$user) {
            // Record failed login attempt
            // The user is not authenticated, so we can't log the attempt with a user_id
            LoginAttempt::create([
                'attempted_at' => now(),
                'successful' => false,
                'ip_address' => $request->ip(),
            ]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Record successful login attempt
        LoginAttempt::create([
            'user_id' => $user->id,
            'attempted_at' => now(),
            'successful' => true,
            'ip_address' => $request->ip(),
        ]);

        // Return success response with session details
        return response()->json([
            'status' => 200,
            'message' => 'Login successful.',
            'session_token' => $user->session_token, // Assuming AuthService::attemptLogin() sets these
            'session_expiration' => $user->session_expiration, // Assuming AuthService::attemptLogin() sets these
        ], 200);
    }

    public function logout(Request $request)
    {
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
}
