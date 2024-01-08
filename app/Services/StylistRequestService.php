
<?php

namespace App\Services;

use App\Models\StylistRequest;
use Illuminate\Support\Facades\Mail;

class StylistRequestService
{
    public function createRequest($validatedData)
    {
        $stylistRequest = StylistRequest::create($validatedData);
        return $stylistRequest->id; // Assuming 'id' is the primary key and unique identifier
    }

    public function sendConfirmationEmail($newEmailAddress)
    {
        $mailConfig = config('mail');
        Mail::send([], [], function ($message) use ($newEmailAddress, $mailConfig) {
            $message->to($newEmailAddress)
                    ->from($mailConfig['from']['address'], $mailConfig['from']['name'])
                    ->subject('Profile Update Confirmation')
                    ->setBody('Your profile has been updated successfully.', 'text/plain');
        });
    }
    
}
