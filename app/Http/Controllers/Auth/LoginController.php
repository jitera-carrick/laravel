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
use Illuminate\Support\Carbon; // Added Carbon
use App\Models\Session;
use App\Services\RecaptchaService; // Import the RecaptchaService
use App\Models\PasswordResetRequest; // Added import for PasswordResetRequest

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'keep_session' => 'sometimes|boolean', // Added keep_session field
            'recaptcha' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();

        if (!$user) {
            LoginAttempt::create([
                'user_id' => null,
                'attempted_at' => now(),
                'successful' => false,
                'ip_address' => $request->ip(),
            ]);
            return response()->json(['error' => 'Email does not exist.'], 400);
        }

        if (!Hash::check($password, $user->password)) {
            LoginAttempt::create([
                'user_id' => $user->id,
                'attempted_at' => now(),
                'successful' => false,
                'ip_address' => $request->ip(),
            ]);
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

            // Additional logic for session management
            $keepSession = $request->input('keep_session', false);
            $sessionExpiration = $keepSession ? now()->addDays(90) : now()->addHours(24);

            $user->forceFill([
                'session_token' => Str::random(60),
                'session_expiration' => $sessionExpiration,
                'is_logged_in' => true,
                'updated_at' => now(),
            ])->save();

            // Return successful login response with session token and expiration
            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'session_token' => $user->session_token,
                'session_expiration' => $user->session_expiration,
            ]);
        } else {
            // Return error response for unverified email
            return response()->json(['error' => 'Email has not been verified.'], 401);
        }
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

    public function cancelLogin(Request $request)
    {
        $sessionToken = $request->cookie('session_token');
        if ($sessionToken) {
            $session = Session::where('session_token', $sessionToken)
                              ->where('is_active', true)
                              ->first();

            if ($session) {
                $session->is_active = false;
                $session->save();

                Cookie::queue(Cookie::forget('session_token'));
            }
        }

        return redirect()->route('screen-tutorial');
    }
}
