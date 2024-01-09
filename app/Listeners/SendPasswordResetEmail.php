
<?php

namespace App\Listeners;

use App\Events\PasswordResetRequested;
use App\Mail\PasswordResetMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetEmail implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  PasswordResetRequested  $event
     * @return void
     */
    public function handle(PasswordResetRequested $event)
    {
        $passwordResetRequest = $event->passwordResetRequest;
        $user = $passwordResetRequest->user;
        $email = new PasswordResetMail($passwordResetRequest->reset_token);
        Mail::to($user->email)->send($email);
    }
}
