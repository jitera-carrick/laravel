``
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Config;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public $reset_token;

    public function __construct($reset_token)
    {
        $this->reset_token = $reset_token;
    }

    public function build()
    {
        return $this->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('Password Reset Request')
                    ->view('emails.password_reset')
                    ->with([
                        'reset_token' => $this->reset_token,
                    ]);
    }
}
