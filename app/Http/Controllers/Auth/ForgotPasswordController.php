<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\PasswordResetToken; // Correct model used for password reset tokens
use App\Notifications\ResetPasswordNotification; // Keep for compatibility
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail; // Correctly added for sending email
use Carbon\Carbon;
use Exception; // Added for exception handling

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

            // Create a new entry in the password_reset_tokens table
            $passwordResetToken = PasswordResetToken::create([
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
                'expires_at' => $expiration,
                'user_id' => $user->id // Associate the token with the user
            ]);

            // Send the password reset email
            try {
                Mail::send('emails.password_reset', ['token' => $token], function ($message) use ($user) {
                    $message->to($user->email);
                    $message->subject('Password Reset Link');
                });

                // Update the status in the password_reset_tokens table to 'sent'
                $passwordResetToken->update(['status' => 'sent']); // Additional field for new feature

                // Return a success response
                return response()->json(['message' => 'Password reset link has been sent to your email address.'], 200);
            } catch (Exception $e) {
                // Log the exception
                report($e);

                // Return a failure response
                return response()->json(['message' => 'Failed to send password reset link.'], 500);
            }
        }

        // Return a response with a generic message
        return response()->json(['message' => 'If your email address is in our database, you will receive a password reset link.']);
    }

    /**
     * Validate the password reset token.
     *
     * @param  string  $token
     * @return bool
     */
    public function validateResetToken(string $token): bool
    {
        $tokenRecord = PasswordResetToken::where('token', $token)->first(); // Updated to use PasswordResetToken model

        if (!$tokenRecord) {
            return false;
        }

        // Check if the 'expires_at' field exists and use it to determine if the token is expired
        $isTokenExpired = Carbon::parse($tokenRecord->expires_at)->isPast();

        return !$isTokenExpired;
    }

    // ... Rest of the existing code in ForgotPasswordController
}
