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

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
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

        $sessionToken = AuthService::generateSessionToken($user);
        $user->update([
            'session_token' => $sessionToken,
            'session_expiration' => now()->addMinutes(config('session.lifetime')),
            'is_logged_in' => true
        ]);

        return ApiResponse::success(['session_token' => $sessionToken]);
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
