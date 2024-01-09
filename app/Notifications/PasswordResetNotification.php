
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $fromAddress = Config::get('mail.from.address');
        $fromName = Config::get('mail.from.name');

        return (new MailMessage)
            ->from($fromAddress, $fromName)
            ->view('emails.password_reset', ['token' => $this->token])
            ->to($notifiable->email)
            ->subject('Password Reset Request');
    }
}
