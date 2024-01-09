
<?php

namespace App\Listeners;

use App\Events\FailedLogin;
use App\Models\LoginAttempt;

class LogFailedLogin
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\FailedLogin  $event
     * @return void
     */
    public function handle(FailedLogin $event)
    {
        LoginAttempt::create([
            'email' => $event->email,
            'attempted_at' => $event->timestamp,
            'successful' => false,
        ]);
    }
}
