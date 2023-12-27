<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Validate the password reset token.
     *
     * @param  string  $token
     * @return bool
     */
    public function validateResetToken(string $token): bool
    {
        $tokenRecord = DB::table('password_reset_tokens')->where('token', $token)->first();

        if (!$tokenRecord) {
            return false;
        }

        $tokenCreationTime = Carbon::parse($tokenRecord->created_at);
        $expireMinutes = config('auth.passwords.users.expire');
        $isTokenExpired = $tokenCreationTime->addMinutes($expireMinutes)->isPast();

        return !$isTokenExpired;
    }

    // ... Rest of the existing code in ForgotPasswordController
}
