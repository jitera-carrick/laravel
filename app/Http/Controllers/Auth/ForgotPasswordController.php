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
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email field
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            // Use the most user-friendly error message
            return response()->json(['message' => $validator->errors()->first('email'), 'reset_requested' => false], 422);
        }

        // Always return a success response to prevent email enumeration
        $responseMessage = 'If your email address exists in our database, you will receive a password recovery link at your email address in a few minutes.';
        return response()->json(['message' => $responseMessage, 'reset_requested' => true], 200);

        // The rest of the code that actually handles sending the email should remain unchanged
        try {
            // Find the user by email
            $user = User::where('email', $request->email)->first();

            if ($user) {
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
            }
        } catch (Exception $e) {
            // If the email fails to send, do not update the status to 'sent'
            // Log the exception if needed
        }
    }

    public function validateResetToken(Request $request)
    {
        // ... (existing code remains unchanged)
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['valid' => false, 'message' => 'Token is required.'], 422);
        }

        try {
            $passwordResetRequest = PasswordResetRequest::where('token', $request->token)
                ->first();

            if (!$passwordResetRequest) {
                return response()->json([
                    'valid' => false,
                    'message' => 'This password reset token does not exist.'
                ], 404);
            }

            $tokenExpired = Carbon::parse($passwordResetRequest->expires_at)->isPast();

            if ($tokenExpired) {
                return response()->json([
                    'valid' => false,
                    'message' => 'This password reset token has expired.'
                ], 404);
            }

            return response()->json([
                'valid' => true,
                'message' => 'The password reset token is valid.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'An error occurred while validating the token.'
            ], 500);
        }
    }

    // ... Rest of the methods remain unchanged
}
