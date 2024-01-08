<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest; // Use the custom LoginRequest
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Session;
use App\Services\RecaptchaService; // Import the RecaptchaService
use Carbon\Carbon; // Import Carbon for date handling
use Illuminate\Http\JsonResponse;

class LoginController extends Controller
{   
    public function login(LoginRequest $request): JsonResponse
    {
        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        $email = $request->input('email');
        $password = $request->input('password');

        if (!$email || !$password) {
            return response()->json(['error' => 'Email and password are required.'], 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Invalid email or password.'], 401);
        }

        if ($user->email_verified_at === null) {
            return response()->json(['error' => 'Email has not been verified.'], 401);
        }

        // Generate new remember_token and update user
        $user->forceFill([
            'remember_token' => Str::random(60),
            'updated_at' => Carbon::now(),
        ])->save();

        // New logic for session management
        $keepSession = $request->input('keep_session', false);
        $sessionExpiry = $keepSession ? Carbon::now()->addDays(90) : Carbon::now()->addDay();
        $sessionToken = Hash::make(Str::random(60));

        $user->update([
            'session_token' => $sessionToken,
            'session_expiration' => $sessionExpiry,
            'is_logged_in' => true,
            'updated_at' => Carbon::now(),
        ]);

        // Return successful login response with remember_token and session management
        return response()->json([
            'status' => 200,
            'session_token' => $sessionToken,
            'session_expiry' => $sessionExpiry->toDateTimeString(),
        ]);
    }

    // ... rest of the existing code for logout and cancelLogin methods
}
