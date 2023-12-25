<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Models\StylistRequest;
use App\Models\Image;
use Exception;
use App\Mail\PasswordResetMailable; // Assuming this Mailable exists
use App\Mail\PasswordResetMail; // Updated to use the correct Mailable as per guideline
use App\Mail\PasswordResetSuccessMail; // Assuming this Mailable class exists
use App\Mail\PasswordResetConfirmationMail; // Assuming this Mailable exists
use App\Mail\PasswordSetConfirmationMail; // Assuming this Mailable exists for password set confirmation

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email field
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            // The new code adds password and token validation here, but it's not needed for sending a reset link
        ]);

        if ($validator->fails()) {
            // Use the most user-friendly error message
            return response()->json(['message' => $validator->errors()->first('email'), 'reset_requested' => false], 422);
        }

        try {
            // Find the user by email
            $user = User::where('email', $request->email)->first();

            // Always display a message indicating that a password reset email has been sent
            // to prevent guessing of registered email addresses.
            $responseMessage = 'If your email address is in our database, you will receive a password reset link shortly.';

            if (!$user) {
                return response()->json(['message' => $responseMessage, 'reset_requested' => true], 200);
            }

            // Generate a unique token
            $token = Str::random(60);

            // Create or update the password reset request
            $passwordResetRequest = PasswordResetRequest::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'token' => $token,
                    'expires_at' => now()->addMinutes(config('auth.passwords.users.expire')),
                    'status' => 'pending',
                ]
            );

            // Send the password reset email
            Mail::to($user->email)->send(new PasswordResetMailable($token));

            // Update the status to 'sent' after the email is successfully sent
            $passwordResetRequest->status = 'sent';
            $passwordResetRequest->save();

            return response()->json(['message' => $responseMessage, 'reset_requested' => true], 200);
        } catch (Exception $e) {
            // If the email fails to send, do not update the status to 'sent'
            return response()->json(['message' => 'Failed to send password reset email.', 'reset_requested' => false], 500);
        }
    }

    // ... Rest of the methods remain unchanged
}
