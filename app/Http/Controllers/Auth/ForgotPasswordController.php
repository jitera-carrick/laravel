<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\PasswordReset;
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

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Generate a unique reset token and expiration time
            $token = Str::random(60);
            $expiration = Carbon::now()->addMinutes(60);

            // Create a new entry in the password_resets table
            // Check if the table name is 'password_resets' or 'password_reset_tokens'
            // and use the appropriate model.
            $passwordReset = PasswordReset::create([
                'email' => $user->email, // Ensure 'email' field is included for compatibility
                'token' => $token, // Use 'token' instead of 'reset_token' for compatibility
                'created_at' => now(), // Use 'created_at' for compatibility
                'expiration' => $expiration, // Additional field for new feature
                'status' => 'pending' // Additional field for new feature
            ]);

            // Send the password reset email
            $user->notify(new ResetPasswordNotification($token));
        }

        // Return a response with a generic message
        return response()->json(['message' => 'If an account with that email exists, we have sent a password reset link to your email address.']);
    }

    /**
     * Validate the password reset token.
     *
     * @param  string  $token
     * @return bool
     */
    public function validateResetToken(string $token): bool
    {
        $tokenRecord = DB::table('password_resets')->where('token', $token)->first(); // Use 'password_resets' table

        if (!$tokenRecord) {
            return false;
        }

        // Check if the 'expiration' field exists and use it to determine if the token is expired
        $tokenExpirationTime = isset($tokenRecord->expiration) ? Carbon::parse($tokenRecord->expiration) : Carbon::parse($tokenRecord->created_at)->addMinutes(config('auth.passwords.users.expire'));
        $isTokenExpired = $tokenExpirationTime->isPast();

        return !$isTokenExpired;
    }

    // ... Rest of the existing code in ForgotPasswordController
}
