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
use App\Mail\PasswordResetSuccessMail; // Assuming this Mailable class exists
use App\Mail\PasswordResetConfirmationMail; // Assuming this Mailable exists
use Exception;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // Existing code for sendResetLinkEmail method remains unchanged
        // ...
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
        if (!preg_match('/[a-zA-Z]/', $request->password) || !preg_match('/[0-9]/', $request->password)) {
            return response()->json(['message' => 'The password must be a mix of letters and numbers.'], 422);
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

        $user->password = Hash::make($request->password);
        $user->save();

        // Update the password reset request status
        $passwordResetRequest->status = 'completed';
        $passwordResetRequest->save();

        // Send confirmation email
        Mail::to($user->email)->send(new PasswordResetSuccessMail()); // Assuming this Mailable class exists

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
