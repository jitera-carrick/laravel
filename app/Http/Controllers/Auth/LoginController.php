<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Session; // Import the Session model
use App\Services\RecaptchaService; // Import the RecaptchaService
use Exception; // Import Exception class

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

    // Updated cancelLogout method as per the new guidelines
    public function cancelLogout(Request $request)
    {
        try {
            // Assuming the user is authenticated using Oauth2 method with Access Token
            $user = $request->user();
            if (!$user) {
                return response()->json([
                    'status' => 401,
                    'message' => 'User is not authenticated.'
                ], 401);
            }

            $activeSession = $user->sessions()->where('is_active', true)->first();

            if ($activeSession) {
                // Optionally, you could also update the session's data here if needed
                return response()->json([
                    'status' => 200,
                    'message' => 'Logout has been cancelled successfully.'
                ], 200);
            } else {
                return response()->json([
                    'status' => 401,
                    'message' => 'No active session found or user is not authenticated.'
                ], 401);
            }
        } catch (Exception $e) {
            // Log the exception if needed
            return response()->json([
                'status' => 500,
                'message' => 'An unexpected error occurred on the server.'
            ], 500);
        }
    }
}
