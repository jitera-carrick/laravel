<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\LoginAttempt; // Import the LoginAttempt model
use App\Services\RecaptchaService; // Import the RecaptchaService

class LoginController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request parameters
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha' => 'required|string', // Add recaptcha validation
        ]);

        // Validate the recaptcha token
        $recaptchaValid = RecaptchaService::validateToken($credentials['recaptcha']);
        if (!$recaptchaValid) {
            return response()->json(['message' => 'Invalid recaptcha.'], 401);
        }

        // Check if the user exists and email is verified
        $user = User::where('email', $credentials['email'])->first();
        if (!$user) {
            // Record the failed login attempt for non-existing user
            LoginAttempt::create([
                'attempted_at' => now(),
                'successful' => false,
                'ip_address' => $request->ip(),
                'user_id' => null, // No user ID since the user does not exist
            ]);
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        // Record the login attempt
        $loginAttemptData = [
            'attempted_at' => now(),
            'successful' => false, // Assume failure until proven successful
            'ip_address' => $request->ip(),
            'user_id' => $user->id,
        ];

        if (!$user->hasVerifiedEmail()) {
            LoginAttempt::create($loginAttemptData);
            return response()->json(['message' => 'Email not verified.'], 401);
        }

        // Attempt to log in the user
        if (Auth::attempt($credentials)) {
            $loginAttemptData['successful'] = true;
            LoginAttempt::create($loginAttemptData);

            // Check if password reset is required
            if ($user->password_reset_required) {
                return response()->json(['message' => 'Password reset required.'], 403);
            }

            // Generate a new access token
            $token = $user->createToken('authToken')->plainTextToken;

            // Return success response with the access token
            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'access_token' => $token
            ], 200);
        } else {
            LoginAttempt::create($loginAttemptData);
            // Return error response if the login attempt fails
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }
    }
}
