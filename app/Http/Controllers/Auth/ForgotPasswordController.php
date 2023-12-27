<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\PasswordReset;
use App\Notifications\ResetPasswordNotification; // Keep for compatibility
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail; // Added for the new feature
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

            // Create a new entry in the password_resets table
            $passwordReset = PasswordReset::create([
                'email' => $user->email, // Ensure 'email' field is included for compatibility
                'token' => $token, // Use 'token' instead of 'reset_token' for compatibility
                'created_at' => now(), // Use 'created_at' for compatibility
                'expiration' => $expiration, // Additional field for new feature
                'status' => 'pending' // Additional field for new feature
            ]);

            // Send the password reset email
            try {
                Mail::send('emails.password_reset', ['token' => $token], function ($message) use ($user) {
                    $message->to($user->email);
                    $message->subject('Password Reset Link');
                });

                // Update the status in the password_resets table to 'sent'
                $passwordReset->update(['status' => 'sent']);

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
