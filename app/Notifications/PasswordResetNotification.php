
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\PasswordResetRequest;

class PasswordResetNotification extends Notification
{
    use Queueable;

    protected $passwordResetRequest;

    public function __construct(PasswordResetRequest $passwordResetRequest)
    {
        $this->passwordResetRequest = $passwordResetRequest;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('You are receiving this email because we received a password reset request for your account.')
                    ->action('Reset Password', url('/password-reset/'.$this->passwordResetRequest->reset_token))
                    ->line('If you did not request a password reset, no further action is required.');
    }
}
