
<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Models\HairStylistRequest;
use App\Models\Request;
use App\Models\RequestImage;
use Exception;

class RequestService
{
    public function deleteRequestImage($request_image_id)
    {
        // Use RequestImage model to find the image by request_image_id
        $image = RequestImage::find($request_image_id);
        if (!$image) {
            throw new Exception("Image not found.");
        }

        // Check if the image is associated with any hair stylist request
        $hairStylistRequest = HairStylistRequest::where('request_image_id', $request_image_id)->first();
        if ($hairStylistRequest) {
            $hairStylistRequest->request_image_id = null;
            $hairStylistRequest->save();
        }

        // Delete the image record
        $image->delete();
        return "Image has been successfully deleted.";
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

    // Rest of the RequestService class...
}
