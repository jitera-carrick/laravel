
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

class UserProfileUpdatedNotification extends Notification
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
                    ->subject('Profile Update Confirmation')
                    ->greeting('Hello!')
                    ->line('Your profile has been successfully updated.')
                    ->line('If you did not request this change, please contact us immediately.');
    }
}
