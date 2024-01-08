
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\StylistRequest;

class RequestPolicy
{
    public function cancel(User $user, StylistRequest $stylistRequest)
    {
        return $user->id === $stylistRequest->user_id;
    }
}
