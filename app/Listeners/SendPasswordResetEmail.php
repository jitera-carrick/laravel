
<?php

namespace App\Listeners;

use App\Events\PasswordResetRequested;
use App\Models\PasswordResetRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetEmail
{
    public function handle(PasswordResetRequested $event)
    {
        $passwordResetRequest = $event->passwordResetRequest;
        $user = $passwordResetRequest->user;
        $resetToken = $passwordResetRequest->reset_token;

        // Assuming Mail facade is set up with a mailable for password reset
        Mail::to($user->email)->send(new \App\Mail\PasswordResetMail($resetToken));
    }
}
