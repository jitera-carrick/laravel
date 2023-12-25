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
use Carbon\Carbon;
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
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first('email'), 'reset_requested' => false], 422);
        }

        try {
            // Find the user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User does not exist.', 'reset_requested' => false], 404);
            }

            // Generate a unique token
            $token = Str::random(60);

            // Create a new password reset request
            $passwordResetRequest = new PasswordResetRequest([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addMinutes(config('auth.passwords.users.expire')),
                'status' => 'pending',
            ]);
            $passwordResetRequest->save();

            // Send the password reset email
            // Assuming a Mailable class named 'PasswordResetMailable' exists
            Mail::to($user->email)->send(new PasswordResetMailable($token));

            return response()->json(['message' => 'Password reset email sent.', 'reset_requested' => true], 200);
        } catch (Exception $e) {
            // Handle any exceptions that occur during the process
            return response()->json(['message' => 'Failed to send password reset email.', 'reset_requested' => false], 500);
        }
    }

    public function validateResetToken(Request $request)
    {
        // ... (existing validateResetToken method code remains unchanged)
    }

    public function verifyEmail(Request $request)
    {
        // Validate the input parameters
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'remember_token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first(), 'verification_successful' => false], 422);
        }

        try {
            // Retrieve the user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User not found.', 'verification_successful' => false], 404);
            }

            // Check if the remember_token matches
            if ($user->remember_token !== $request->remember_token) {
                return response()->json(['message' => 'Invalid token.', 'verification_successful' => false], 401);
            }

            // Update the user's email_verified_at field and clear the remember_token
            $user->email_verified_at = Carbon::now();
            $user->remember_token = null;
            $user->save();

            return response()->json(['message' => 'Email verified successfully.', 'verification_successful' => true], 200);
        } catch (Exception $e) {
            // Handle any exceptions that occur during the process
            return response()->json(['message' => 'Email verification failed.', 'verification_successful' => false], 500);
        }
    }

    // New method to verify email and set new password
    public function verifyEmailAndSetPassword(Request $request)
    {
        // ... (new verifyEmailAndSetPassword method code remains unchanged)
    }

    // ... (other methods remain unchanged)
}
