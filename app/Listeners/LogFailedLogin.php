
<?php

namespace App\Listeners;

use App\Events\FailedLogin;
use App\Models\LoginAttempt;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class LogFailedLogin
{
    public function handle(FailedLogin $event)
    {
        DB::beginTransaction();
        try {
            LoginAttempt::create([
                'email' => $event->email,
                'attempted_at' => now(),
                'successful' => false
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            // Log the exception
            // This is a placeholder for the logging logic
            // Replace this comment with the actual logging code
            // Log::error($e->getMessage());
            throw $e;
        }
    }
}
