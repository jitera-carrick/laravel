
<?php

namespace App\Listeners;
use Illuminate\Support\Carbon;

use App\Events\FailedLogin;
use App\Models\LoginAttempt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogFailedLogin
{
    public function handle(FailedLogin $event)
    {
        $loginAttempt = LoginAttempt::create([
            'email' => $event->email,
            'attempted_at' => Carbon::now(),
            'successful' => false
        ]);
    }
}
