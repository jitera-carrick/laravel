
<?php

namespace App\Listeners;

use App\Events\FailedLogin;
use App\Models\LoginAttempt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogFailedLogin
{
    public function handle(FailedLogin $event)
    {
        LoginAttempt::create([
            'email' => $event->email,
            'attempted_at' => now(),
            'successful' => false
        ]);
    }
}
