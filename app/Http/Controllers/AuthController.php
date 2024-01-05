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
        // Check if the request is an instance of LoginRequest to use the new validation
        if ($request instanceof LoginRequest) {
            $email = $request->input('email');
            $password = $request->input('password');

            $user = User::where('email', $email)->first();

            if (!$user) {
                return ApiResponse::error('Email does not exist.', 400);
            }

            $hashedPassword = HashHelper::hash($password, $user->password_salt);

            if (!hash_equals($hashedPassword, $user->password_hash)) {
                return ApiResponse::error('Incorrect password.', 401);
            }
        } else {
            // Use the existing validation for other types of requests
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
        }

        $sessionToken = AuthService::generateSessionToken($user);
        $user->update([
            'session_token' => $sessionToken,
            'session_expiration' => now()->addMinutes(config('session.lifetime')),
            'is_logged_in' => true
        ]);

        return ApiResponse::success(['session_token' => $sessionToken, 'message' => 'Login successful.']);
    }

    public function logout(Request $request)
    {
        try {
            // Check if the request is an instance of LogoutRequest to use the new validation
            if ($request instanceof LogoutRequest) {
                $sessionToken = $request->input('session_token');
                if (!$sessionToken) {
                    return ApiResponse::error('Invalid session token.', 400);
                }

                $user = User::where('session_token', $sessionToken)->first();

                if (!$user) {
                    return ApiResponse::error('Invalid session token.', 401);
                }

                if ($user->session_expiration < Carbon::now()) {
                    return ApiResponse::error('Invalid session token.', 401);
                }
            } else {
                // Use the existing logic for other types of requests
                $user = User::where('session_token', $request->session_token)->first();

                if (!$user) {
                    return ApiResponse::error('Logout attempt failed. No user found with the provided session token.');
                }
            }

            $user->update([
                'session_token' => null,
                'is_logged_in' => false,
                'session_expiration' => Carbon::now(),
            ]);

            return ApiResponse::success('Logout successful.');
        } catch (\Exception $e) {
            return Handler::renderException($e);
        }
    }

    // ... other methods ...
}
