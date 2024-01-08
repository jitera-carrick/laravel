
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class VerifyEmailNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject(Lang::get('Verify Email Address'))
                    ->line(Lang::get('Please click the button below to verify your email address.'))
                    ->action(Lang::get('Verify Email Address'), url('/email/verify/'.$notifiable->getEmailVerificationUrl().'?username='.$notifiable->username))
                    ->line(Lang::get('If you did not create an account, no further action is required.'));
    }
}
