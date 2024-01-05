
<?php

use App\Models\PasswordResetToken;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordResetNotification extends Notification
{
    protected $token;

    public function __construct(PasswordResetToken $token)
    {
        $this->token = $token;
    }

    public function toMail($notifiable)
    {
        $url = url('/password-reset/' . $this->token->token);
        return (new MailMessage)->view('mail-password_reset', ['url' => $url]);
    }
}
