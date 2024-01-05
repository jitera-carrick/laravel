<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\LogoutRequest;
use App\Services\AuthService;
use App\Services\HashHelper;
use App\Helpers\ApiResponse;
use App\Exceptions\Handler;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha' => 'required',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user || !Hash::check($validatedData['password'], $user->password)) {
            return ApiResponse::error('Invalid email or password.', 401);
        }

        $recaptchaIsValid = AuthService::validateRecaptcha($validatedData['recaptcha']);
        if (!$recaptchaIsValid) {
            return ApiResponse::error('Invalid recaptcha.', 422);
        }

        $sessionToken = AuthService::generateSessionToken($user);
        $user->update([
            'session_token' => $sessionToken,
            'session_expiration' => now()->addMinutes(config('session.lifetime')),
            'is_logged_in' => true
        ]);

        return ApiResponse::success(['session_token' => $sessionToken, 'message' => 'Login successful.']);
    }

    public function logout(LogoutRequest $request)
    {
        try {
            $user = User::where('session_token', $request->session_token)->first();

            if ($user) {
                $user->update([
                    'session_token' => null,
                    'is_logged_in' => false,
                    'session_expiration' => Carbon::now(),
                ]);

                return ApiResponse::success('Logout successful.');
            }

            return ApiResponse::error('Logout attempt failed. No user found with the provided session token.');
        } catch (\Exception $e) {
            return Handler::renderException($e);
        }
    }

    // ... other methods ...
}
