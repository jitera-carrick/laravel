<?php

namespace App\Services;

use App\Models\PasswordResetToken;
use App\Models\User;
use App\Helpers\TokenHelper;
use Illuminate\Support\Facades\DB;

class PasswordResetService
{
    public function createResetToken($email)
    {
        $user = User::where('email', $email)->firstOrFail();
        $tokenHelper = new TokenHelper();
        $token = $tokenHelper->generateSessionToken();
        $expiresAt = now()->addHour();

        // Start a transaction to ensure atomicity
        DB::beginTransaction();
        try {
            $passwordResetToken = PasswordResetToken::createToken($email, $token, $expiresAt);
            DB::commit();
            return $passwordResetToken;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
