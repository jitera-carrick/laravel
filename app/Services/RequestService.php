
<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\HairStylistRequest;
use App\Models\Request;
use App\Models\RequestImage;
use App\Models\PasswordResetRequest;
use App\Models\PasswordResetToken; // Added for the validateResetToken method
use Exception;

class RequestService
{
    public function deleteRequestImage($request_id, $image_id = null)
    {
        // Check if the request exists
        $request = Request::find($request_id);
        if (!$request) {
            throw new Exception("Request not found.");
        }

        // If image_id is provided, use it, otherwise use request_id as request_image_id
        $image_id = $image_id ?? $request_id;

        // Find the image associated with the request
        $image = RequestImage::where('request_id', $request_id)->where('id', $image_id)->first();
        if (!$image) {
            throw new Exception("Image not found or does not belong to the request.");
        }

        // Check for linked HairStylistRequest and unlink if necessary
        $hairStylistRequest = HairStylistRequest::where('request_image_id', $image_id)->first();
        if ($hairStylistRequest) {
            $hairStylistRequest->request_image_id = null;
            $hairStylistRequest->save();
        }

        // Delete the image file from storage if exists
        if (Storage::exists($image->image_path)) {
            Storage::delete($image->image_path);
        }

        // Delete the image record
        $image->delete();
        return true;
    }

    public function sendResetLinkEmail($user, $token)
    {
        try {
            $resetUrl = url('/password/reset/' . $token);
            Mail::send('emails.reset', ['user' => $user, 'resetUrl' => $resetUrl], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Password Reset Link');
            });

            // Log the email sending action
            $emailLog = new EmailLog([
                'email_type' => 'password_reset',
                'sent_at' => now(),
                'user_id' => $user->id,
            ]);
            $emailLog->save();

            return ['status' => 'success', 'message' => 'Reset link sent to email.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => 'Failed to send reset link.'];
        }
    }

    public function logPasswordResetAction($request_time, $reset_token, $status, $user_id)
    {
        try {
            $passwordResetRequest = new PasswordResetRequest();
            $passwordResetRequest->request_time = $request_time;
            $passwordResetRequest->reset_token = $reset_token;
            $passwordResetRequest->status = $status;
            $passwordResetRequest->user_id = $user_id;
            $passwordResetRequest->save();
        } catch (Exception $e) {
            // Handle the exception as needed, possibly logging or rethrowing
            throw $e;
        }
    }

    public function validateResetToken(string $token): bool
    {
        $passwordResetToken = PasswordResetToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$passwordResetToken) {
            throw new Exception("Invalid or expired reset token.");
        }

        return true;
    }

    // Rest of the RequestService class...
}
