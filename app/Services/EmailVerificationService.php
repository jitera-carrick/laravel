
<?php

namespace App\Services;

use App\Models\EmailVerificationToken;
use App\Models\User;
use App\Exceptions\EmailVerificationException;
use Illuminate\Support\Carbon;

class EmailVerificationService
{
    public function verifyToken($token)
    {
        $emailVerificationToken = EmailVerificationToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$emailVerificationToken || $emailVerificationToken->used || $emailVerificationToken->expires_at->isPast()) {
            throw new EmailVerificationException('The email verification token is invalid or has expired.');
        }

        $user = $emailVerificationToken->user;
        $user->email_verified_at = Carbon::now();
        $user->save();

        $emailVerificationToken->used = true;
        $emailVerificationToken->save();

        return true;
    }
}
