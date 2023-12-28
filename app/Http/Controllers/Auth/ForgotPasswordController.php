<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;

class ForgotPasswordController extends Controller
{
    /**
     * Handle sending the password reset link to the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email field
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if the email exists in the database
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email does not exist.'], 404);
        }

        // Generate a unique token for the password reset
        $token = Str::random(60);

        // Calculate the expires_at timestamp
        $expiresAt = now()->addMinutes(Config::get('auth.passwords.users.expire'));

        // Store the token in the password_reset_tokens table
        $passwordResetToken = new PasswordResetToken([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => $expiresAt,
            'used' => false,
            'user_id' => $user->id,
        ]);
        $passwordResetToken->save();

        // Send the password reset email to the user
        try {
            // You can create a new Mailable class or use a notification to send the email
            // For example: Mail::to($request->email)->send(new PasswordResetMail($token));
            // This is a placeholder line for sending email, replace with actual email sending logic
            Mail::raw("Your password reset link: " . route('password.reset', ['token' => $token]), function ($message) use ($request) {
                $message->to($request->email)->subject('Password Reset Link');
            });

            return response()->json(['message' => 'Password reset link has been sent to your email.'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send password reset link.'], 500);
        }
    }
}
