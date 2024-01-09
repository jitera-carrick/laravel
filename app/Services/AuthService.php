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
    public function login($email, $password, $keepSession)
    {
        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($password, $user->password_hash)) {
            // Updated the exception message to be more generic and cover both cases.
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

    public function createPasswordResetRequest(User $user)
    {
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
