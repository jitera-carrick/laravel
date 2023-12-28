<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmailRequest;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    // Add your new method below
    public function initiatePasswordReset(Request $request)
    {
        // Validate the email format
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid email address.'], 400);
        }

        // Check if the email exists in the users table
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'No user found with that email.'], 404);
        }

        // Generate a unique password reset token
        $token = Str::random(60);

        // Store the token in the password_reset_tokens table
        $passwordResetToken = new PasswordResetToken([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => now()->addHours(2),
            'used' => false,
            'user_id' => $user->id
        ]);
        $passwordResetToken->save();

        // Send the password reset token to the user's email
        try {
            $user->notify(new ResetPasswordNotification($token));
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['message' => 'Failed to send password reset email.'], 500);
        }

        // Return a response indicating the password reset email has been sent successfully
        return response()->json(['message' => 'Password reset instructions have been sent to your email.'], 200);
    }

    // The sendResetLinkEmail method remains unchanged
    public function sendResetLinkEmail(EmailRequest $request)
    {
        // Validate the email format
        $validatedData = $request->validated();

        // Check if the email exists in the users table
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'No user found with that email.'], 404);
        }

        // Generate a unique password reset token
        $token = Str::random(60);

        // Store the token in the password_reset_tokens table
        $passwordResetToken = new PasswordResetToken([
            'email' => $user->email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => now()->addHours(2),
            'used' => false,
            'user_id' => $user->id
        ]);
        $passwordResetToken->save();

        // Send the password reset token to the user's email
        try {
            $user->notify(new ResetPasswordNotification($token));
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['message' => 'Failed to send password reset email.'], 500);
        }

        // Return a response indicating the password reset email has been sent successfully
        return response()->json(['message' => 'Password reset email sent successfully.'], 200);
    }

    // ... Rest of the existing code in the ForgotPasswordController
}
