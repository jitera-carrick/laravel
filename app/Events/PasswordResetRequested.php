
<?php

namespace App\Events;

use App\Models\PasswordResetRequest;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PasswordResetRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $passwordResetRequest;

    public function __construct(PasswordResetRequest $passwordResetRequest)
    {
        $this->passwordResetRequest = $passwordResetRequest;
    }
}
