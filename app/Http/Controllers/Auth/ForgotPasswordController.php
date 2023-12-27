<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\PasswordResetToken;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email field
        $request->validate(['email' => 'required|email']);

        // Check if the email exists in the database
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Generate a unique token
            $token = Str::random(60);

            // Store the token in the password_reset_tokens table
            PasswordResetToken::create([
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
                'user_id' => $user->id
            ]);

            // Send the password reset email
            $user->notify(new ResetPasswordNotification($token));
        }

        // Return a response indicating that the email has been sent
        return response()->json(['message' => 'A password reset email has been sent.'], 200);
    }

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
