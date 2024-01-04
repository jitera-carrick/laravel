
<?php

namespace App\Listeners;

use App\Events\StylistRequestSubmitted;
use App\Notifications\StylistRequestNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use App\User; // Import the User model

class SendStylistRequestNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(StylistRequestSubmitted $event)
    {
        $stylistRequest = $event->stylistRequest;
        $admins = User::where('is_admin', true)->get(); // Assuming 'is_admin' attribute exists to identify system administrators
        Notification::send($admins, new StylistRequestNotification($stylistRequest->id, $stylistRequest->request_time));
    }
}
