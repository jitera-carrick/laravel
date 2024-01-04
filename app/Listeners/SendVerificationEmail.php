
<?php

namespace App\Listeners;

use App\Events\UserRegistered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use App\Models\EmailVerificationToken;

class SendVerificationEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  UserRegistered  $event
     * @return void
     */
    public function handle(UserRegistered $event)
    {
        $user = $event->user;
        $token = EmailVerificationToken::create([
            'user_id' => $user->id,
            'token' => str_random(60), // Assuming str_random is a helper function to generate a random string
            'expires_at' => now()->addHours(24),
            'used' => false
        ]);

        Mail::to($user->email)->send(new VerificationEmail($user, $token));
    }
}
