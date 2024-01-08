
<?php

use App\Models\EmailVerificationToken;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailNotification extends Notification
{
    use Queueable;
    protected $verificationToken;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $token
     * @return void
     */
    public function __construct($token)
    {
        $this->verificationToken = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('Please click the button below to verify your email address with the new token format.')
                    ->action('Verify Email Address', url('/email/verify/'.$this->verificationToken->token))
                    ->line('If you did not create an account, no further action is required.');
    }
}
