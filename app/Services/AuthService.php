<?php

namespace App\Services;

use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Helpers\TokenHelper;
use Illuminate\Support\Facades\Hash;
use App\Notifications\PasswordResetNotification;
use App\Models\LoginAttempt;

class AuthService
{
    // Method to validate user credentials and create a session token
    public function login($email, $password, $keepSession)
    {
        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($password, $user->password_hash)) {
            throw new \Exception('Login failed. Please check your credentials and try again.');
        }

        $tokenHelper = new TokenHelper();
        $sessionToken = $tokenHelper->generateSessionToken();
        $sessionExpiration = $tokenHelper->calculateSessionExpiration($keepSession);

        $user->session_token = $sessionToken;
        $user->session_expiration = $sessionExpiration;
        $user->keep_session = $keepSession;
        $user->save();

        return $sessionToken;
    }

    // Method to create a session token for a user
    public function createSessionToken(User $user, $keepSession)
    {
        $tokenHelper = new TokenHelper();
        $sessionToken = $tokenHelper->generateSessionToken();
        $sessionExpiration = $tokenHelper->calculateSessionExpiration($keepSession);

        $user->session_token = $sessionToken;
        $user->session_expiration = $sessionExpiration;
        $user->keep_session = $keepSession;
        $user->save();

        return $sessionToken;
    }

    public function cancelLoginProcess()
    {
        try {
            $loginAttempts = LoginAttempt::where('user_id', auth()->id())
                                         ->where('successful', false)
                                         ->get();

            foreach ($loginAttempts as $attempt) {
                $attempt->delete();
            }
        } catch (\Exception $e) {
            // Handle exception if needed
        }
    }

    // Method to create a password reset request
    public function createPasswordResetRequest($email)
    {
        $user = User::where('email', $email)->first();
        if (!$user) return null;

        $tokenHelper = new TokenHelper();
        $resetToken = $tokenHelper->generateSessionToken();
        $tokenExpiration = now()->addHour();

        $passwordResetRequest = new PasswordResetRequest([
            'user_id' => $user->id,
            'reset_token' => $resetToken,
            'token_expiration' => $tokenExpiration,
        ]);
        $passwordResetRequest->save();

        $user->notify(new PasswordResetNotification($resetToken));

        return $passwordResetRequest;
    }

    // Method to terminate the login process for a user
    public function terminateLoginProcess($userId)
    {
        $loginAttempts = LoginAttempt::where('user_id', $userId)
                                     ->where('successful', false)
                                     ->get();

        foreach ($loginAttempts as $attempt) {
            $attempt->delete();
        }
    }
}
