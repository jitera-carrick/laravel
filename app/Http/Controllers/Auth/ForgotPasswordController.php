<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\PasswordResetToken;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

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
                'user_id' => $user->id, // Associate the token with the user
                'status' => 'pending' // Added from new code to track status
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

    // ... Rest of the existing code in ForgotPasswordController

    /**
     * Send a password reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPasswordResetLink(Request $request)
    {
        // Validate the email field using Laravel's built-in validation
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid email format.'], 400);
        }

        $email = $request->input('email');

        // Find the user by email
        $user = User::where('email', $email)->first();

        if ($user) {
            // Generate a unique reset token and expiration time
            $token = Str::random(60);
            $expiration = Carbon::now()->addMinutes(60);

            // Create a new entry in the password_reset_tokens table
            try {
                $passwordResetToken = PasswordResetToken::create([
                    'email' => $user->email,
                    'token' => $token,
                    'created_at' => now(),
                    'expires_at' => $expiration,
                    'user_id' => $user->id,
                ]);

                // Send the password reset email
                Mail::send('emails.password_reset', ['token' => $token], function ($message) use ($user) {
                    $message->to($user->email);
                    $message->subject('Password Reset Link');
                });

                // Return a success response
                return response()->json(['message' => 'Password reset link has been sent to your email address.'], 200);
            } catch (Exception $e) {
                // Log the exception
                Log::error($e->getMessage());

                // Return a failure response
                return response()->json(['message' => 'Failed to send password reset link.'], 500);
            }
        }

        // Return a response with a generic message
        return response()->json(['message' => 'If your email address is in our database, you will receive a password reset link.']);
    }

    // ... Rest of the existing code in ForgotPasswordController
}
