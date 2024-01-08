
<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\StylistRequest;

class StylistRequestService
{
    public function createRequest($validatedData)
    {
        $stylistRequest = StylistRequest::create($validatedData);
        return $stylistRequest->id; // Assuming 'id' is the primary key and unique identifier
    }

    public function cancelRequest(int $userId, int $requestId)
    {
        $request = HairStylistRequest::where('id', $requestId)->where('user_id', $userId)->first();

        if (!$request) {
            return false;
        }

        $request->status = 'canceled';
        $request->save();

        return true;
    }
}
