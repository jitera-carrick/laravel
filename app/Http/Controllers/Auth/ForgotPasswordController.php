<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\PasswordResetToken;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator; // Import the Validator facade
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Import Log facade

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
     * Handle the password reset process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        // Validate the input fields
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'new_password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Find the password reset token for the user
        $tokenRecord = PasswordResetToken::where('email', $request->email)->latest()->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Invalid password reset token.'], 400);
        }

        // Check if the token has expired
        if (Carbon::parse($tokenRecord->expires_at)->isPast()) {
            return response()->json(['message' => 'Password reset token has expired.'], 400);
        }

        // Update the user's password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Invalidate the password reset token
        $tokenRecord->update(['status' => 'used']);

        return response()->json(['message' => 'Password has been successfully reset.'], 200);
    }

    // ... Rest of the existing code in ForgotPasswordController
}
