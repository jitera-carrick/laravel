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
                'expires_at' => now()->addMinutes(60), // Set expiration to 60 minutes from now
                'status' => 'pending', // Initial status is 'pending'
            ]);
            $passwordResetRequest->save();

            // Send the password reset email
            Mail::to($user->email)->send(new PasswordResetMail($token)); // Use the correct Mailable

            // Update the status of the password reset request to 'sent'
            $passwordResetRequest->status = 'sent';
            $passwordResetRequest->save();

            return response()->json(['message' => 'Password reset email sent.', 'reset_requested' => true], 200);
        } catch (Exception $e) {
            // Handle any exceptions that occur during the process
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
        // Validate the input
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'password' => 'required|string|confirmed|min:6',
            'password_confirmation' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Ensure the new password complies with the password policy
        if (!preg_match('/^(?=.*[a-zA-Z])(?=.*\d).{6,}$/', $request->password)) {
            return response()->json(['message' => 'Password does not meet the policy requirements.'], 422);
        }

        // Retrieve the password reset request
        $passwordResetRequest = PasswordResetRequest::where('token', $request->token)
            ->where('expires_at', '>', now())
            ->where('status', 'pending')
            ->first();

        if (!$passwordResetRequest) {
            return response()->json(['message' => 'Invalid or expired password reset token.'], 404);
        }

        // Find the user and update the password
        $user = $passwordResetRequest->user;
        if ($request->password === $user->email || $request->password === $user->id) {
            return response()->json(['message' => 'The password cannot be the same as your email address or ID.'], 422);
        }

        // Ensure the new password is different from the user's email
        if (Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'New password cannot be the same as the current password.'], 422);
        }

        $user->password = Hash::make($request->password);
        $user->email_verified_at = now(); // Mark the email as verified
        $user->save();

        // Update the password reset request status or delete it
        if (isset($passwordResetRequest->status)) {
            $passwordResetRequest->status = 'completed';
            $passwordResetRequest->save();
        } else {
            $passwordResetRequest->delete(); // Delete the password reset request to invalidate the token
        }

        // Send confirmation email
        if (class_exists(PasswordSetConfirmationMail::class)) {
            Mail::to($user->email)->send(new PasswordSetConfirmationMail()); // Send the password set confirmation email
        } else {
            Mail::to($user->email)->send(new PasswordResetConfirmationMail()); // Send the password reset confirmation email
        }

        // Return a success response
        return response()->json(['message' => 'Your password has been successfully updated.'], 200);
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

    // Method to handle image uploads and create image records
    private function handleImageUploads(Request $request, $stylistRequestId)
    {
        // Existing code for handleImageUploads method remains unchanged
        // ...
    }
}
