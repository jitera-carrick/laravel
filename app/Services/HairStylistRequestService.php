
<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\User;
use App\Models\RequestImage;

class HairStylistRequestService
{
    public function createRequest($user_id)
    {
        $hairStylistRequest = new HairStylistRequest();
        $hairStylistRequest->user_id = $user_id;
        $hairStylistRequest->status = 'pending';
        $hairStylistRequest->save();
        return $hairStylistRequest;
    }

    // ... rest of the existing methods ...
}
