<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth; // Import the Auth facade
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Session; // Import the Session model
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

    public function cancelLogout(Request $request)
    {
        $sessionToken = $request->cookie('session_token');

        if (!$sessionToken) {
            return response()->json(['message' => 'No session token provided.'], 400);
        }

        $session = Session::where('session_token', $sessionToken)->first();

        if ($session && $session->is_active) {
            return response()->json(['message' => 'Logout cancelled. You are still logged in.']);
        }

        return response()->json(['message' => 'Session not found or inactive.'], 404);
    }

    // New logout method
    public function logout(Request $request)
    {
        $sessionToken = $request->input('session_token');

        if (!$sessionToken) {
            return response()->json(['message' => 'Session token is required.'], 400);
        }

        $session = Session::where('session_token', $sessionToken)->first();

        if (!$session) {
            return response()->json(['message' => 'Session not found.'], 401);
        }

        if ($session->is_active) {
            Auth::logout();
            $session->is_active = false;
            $session->save();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully logged out.'
            ]);
        } else {
            return response()->json(['message' => 'Session is already inactive.'], 401);
        }
    }
}
