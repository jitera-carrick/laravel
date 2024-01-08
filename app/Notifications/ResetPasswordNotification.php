
<?php

use App\Mail\ResetPasswordMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Mail;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function sendResetLinkEmail($token)
    {
        $resetUrl = url(config('app.url') . route('password.reset', ['token' => $token], false));
        $email = $this->notifiable->email;
        Mail::to($email)->send(new ResetPasswordMail($resetUrl));
    }

    // ... Rest of the class
}
