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
                'expires_at' => now()->addMinutes(config('auth.passwords.users.expire')), // Use config value for consistency
                'status' => 'pending',
            ]);
            $passwordResetRequest->save();

            // Send the password reset email
            Mail::to($user->email)->send(new PasswordResetMailable($token)); // Use PasswordResetMailable as per new code

            // Update the status to 'sent' after the email is successfully sent
            $passwordResetRequest->status = 'sent';
            $passwordResetRequest->save();

            return response()->json(['message' => 'Password reset email sent.', 'reset_requested' => true], 200);
        } catch (Exception $e) {
            // If the email fails to send, do not update the status to 'sent'
            return response()->json(['message' => 'Failed to send password reset email.', 'reset_requested' => false], 500);
        }
    }

    public function validateResetToken(Request $request)
    {
        // Existing code for validateResetToken method remains unchanged
        // ...
    }

    public function resetPassword(Request $request)
    {
        // Existing code for resetPassword method remains unchanged
        // ...
    }

    public function setUserPassword(Request $request)
    {
        // Existing code for setUserPassword method remains unchanged
        // ...
    }

    public function submitStylistRequest(Request $request)
    {
        // Existing code for submitStylistRequest method remains unchanged
        // ...
    }

    private function handleImageUploads(Request $request, $stylistRequestId)
    {
        // Existing code for handleImageUploads method remains unchanged
        // ...
    }
}
