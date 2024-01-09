
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

    private $resetToken;

    public function __construct($token)
    {
        $this->resetToken = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $mailConfig = Config::get('mail');
        $resetUrl = url('/password/reset/' . $this->resetToken);

        return (new MailMessage)
            ->subject('Password Reset Notification')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->action('Reset Password', $resetUrl)
            ->line('If you did not request a password reset, no further action is required.');
    }
}
