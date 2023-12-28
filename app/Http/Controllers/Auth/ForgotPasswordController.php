<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{
    // Add your new method below
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email address
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid email address.'], 422);
        }

        // Check if the email exists in the database
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email address not found.'], 404);
        }

        // Generate a unique token
        $token = Str::random(60);

        // Store the token in the password_reset_tokens table
        try {
            $user->passwordResetTokens()->create([
                'email' => $request->email,
                'token' => $token,
                'created_at' => now(),
            ]);

            // Send the password reset token to the user's email address
            $user->notify(new ResetPasswordNotification($token, $request->email));

            return response()->json(['message' => 'Password reset link sent to your email address.'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to create password reset token: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send password reset link.'], 500);
        }
    }

    // ... Rest of the existing code in the ForgotPasswordController
}
