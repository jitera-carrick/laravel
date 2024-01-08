
<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Models\HairStylistRequest;
use App\Models\User;
use App\Models\RequestImage;

class HairStylistRequestService
{
    public function createRequest($data)
    {
        // Check if the user_id corresponds to a valid user
        $user = User::find($data['user_id']);
        if (!$user) {
            throw new \Exception('Invalid user_id provided');
        }

        // If a request_image_id is provided, verify it
        if (isset($data['request_image_id'])) {
            $requestImage = RequestImage::find($data['request_image_id']);
            if (!$requestImage) {
                throw new \Exception('Invalid request_image_id provided');
            }
        }

        // Create a new HairStylistRequest record
        $hairStylistRequest = HairStylistRequest::create($data);

        return $hairStylistRequest;
    }

    public function sendEmailVerification($user, $emailVerificationToken)
    {
        $verificationUrl = url('/email/verify/' . $emailVerificationToken->token);

        Mail::send('emails.verify', ['user' => $user, 'verificationUrl' => $verificationUrl], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Verify Your Email Address');
        });

        if (Mail::failures()) {
            throw new \Exception('Failed to send verification email.');
        }

        return true;
    }

}
