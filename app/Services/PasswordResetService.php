<?php

namespace App\Services;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Helpers\TokenHelper;
use Illuminate\Support\Facades\DB;
use App\Notifications\PasswordResetNotification;

class PasswordResetService
{
    public function createResetToken($email)
    {
        $user = User::where('email', $email)->first();
        $tokenHelper = new TokenHelper();
        $token = $tokenHelper->generateSessionToken();
        $expiresAt = now()->addHour();

        // Start a transaction to ensure atomicity
        DB::beginTransaction();
        try {
            $passwordResetToken = PasswordResetToken::createToken($email, $token, $expiresAt);
            if (!$user) {
                throw new \Exception('User not found.');
            }
            $passwordResetToken->user_id = $user->id;
            $passwordResetToken->save();
            DB::commit();
            return $passwordResetToken;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function createPasswordResetRequest($email)
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            throw new \Exception('Email not found.');
        }

        $tokenHelper = new TokenHelper();
        $resetToken = $tokenHelper->generateSessionToken();
        $tokenExpiration = now()->addHour();

        $passwordResetRequest = new PasswordResetToken([
            'email' => $email,
            'token' => $resetToken,
            'expires_at' => $tokenExpiration,
            'user_id' => $user->id,
        ]);
        $passwordResetRequest->save();

        $user->notify(new PasswordResetNotification($passwordResetRequest));

        return $passwordResetRequest;
    }
}
