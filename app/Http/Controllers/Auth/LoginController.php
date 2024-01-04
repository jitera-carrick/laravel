<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Session;
use App\Services\RecaptchaService; // Import the RecaptchaService
use App\Services\AuthService; // Import the AuthService

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
            $errors = $validator->errors();
            $responseErrors = [];
            if ($errors->has('email')) {
                $responseErrors['email'] = 'Please enter a valid email address.';
            }
            if ($errors->has('password')) {
                $responseErrors['password'] = 'Password cannot be blank.';
            }
            if ($errors->has('recaptcha')) {
                $responseErrors['recaptcha'] = 'Invalid recaptcha.';
            }
            return response()->json($responseErrors, 422);
        }

        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['recaptcha' => 'Invalid recaptcha.'], 401);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Record successful login attempt
            LoginAttempt::create([
                'user_id' => $user->id,
                'attempted_at' => now(),
                'successful' => true,
                'ip_address' => $request->ip(),
            ]);

            if ($user->email_verified_at !== null) {
                $token = AuthService::updateUserLoginStatus($user);

                return response()->json([
                    'status' => 200,
                    'session_token' => $token,
                    'user' => $user,
                ]);
            } else {
                return response()->json(['error' => 'Email has not been verified.'], 401);
            }
        } else {
            // Record failed login attempt
            LoginAttempt::create([
                'user_id' => null,
                'attempted_at' => now(),
                'successful' => false,
                'ip_address' => $request->ip(),
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
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

        try {
            $sessionToken = $request->input('session_token');
            $session = Session::where('session_token', $sessionToken)
                              ->where('is_active', true)
                              ->first();

            if ($session) {
                $session->is_active = false;
                $session->save();

                Cookie::queue(Cookie::forget('session_token'));

                return response()->json([
                    'status' => 200,
                    'message' => 'You have been logged out successfully.'
                ]);
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'Invalid session token.'
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

    // ... other methods ...
}
