<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Str;
use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Models\StylistRequest;
use App\Models\Image;
use Exception;
use Carbon\Carbon;
use App\Mail\PasswordResetMail;
use App\Mail\PasswordResetSuccessMail;
use App\Mail\PasswordResetConfirmationMail;
use App\Mail\PasswordSetConfirmationMail;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // ... (existing sendResetLinkEmail method code remains unchanged)
    }

    public function validateResetToken(Request $request)
    {
        // ... (existing validateResetToken method code remains unchanged)
    }

    public function verifyEmail(Request $request)
    {
        // ... (existing verifyEmail method code remains unchanged)
    }

    public function verifyEmailAndSetPassword(Request $request)
    {
        // ... (existing verifyEmailAndSetPassword method code remains unchanged)
    }

    // ... (other methods remain unchanged)

    public function initiatePasswordReset(Request $request)
    {
        // Validate the email field
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email address format.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first('email')], 422);
        }

        try {
            // Find the user by email
            $user = User::where('email', $request->email)->first();

            // Generate a unique token
            $token = Str::random(60);

            // Create a new password reset request only if user exists
            if ($user) {
                $passwordResetRequest = PasswordResetRequest::create([
                    'user_id' => $user->id,
                    'token' => $token,
                    'expires_at' => now()->addMinutes(config('auth.passwords.users.expire')),
                    'status' => 'pending',
                ]);

                // Send the password reset email
                Mail::to($user->email)->send(new PasswordResetMail($token));
            }

            // Always return the same response regardless of user existence to prevent email enumeration
            return response()->json(['message' => 'If your email address exists in our database, you will receive a password reset link.'], 200);
        } catch (Exception $e) {
            // Handle any exceptions that occur during the process
            return response()->json(['message' => 'Failed to initiate password reset.'], 500);
        }
    }
}
