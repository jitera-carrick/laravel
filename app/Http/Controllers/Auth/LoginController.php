<?php

namespace App\Http\Controllers\Auth;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest; // Use the custom LoginRequest
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Session;
use App\Services\RecaptchaService; // Import the RecaptchaService
use Carbon\Carbon; // Import Carbon for date handling
use Illuminate\Http\Request;

class LoginController extends Controller
{   
    public function login(LoginRequest $request)
    {
        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        $email = $request->validated()['email'];
        $password = $request->validated()['password'];
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email does not exist.'], 400);
        }

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

        // Generate JWT token
        $token = JWTAuth::fromUser($user);
        $tokenExpiry = auth()->factory()->getTTL() * 60; // Get the token expiry time in seconds

        // Return successful login response with JWT token
        return response()->json([
            'status' => 200,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $tokenExpiry
        ]);
    }

    // ... rest of the existing methods in the class ...
}
