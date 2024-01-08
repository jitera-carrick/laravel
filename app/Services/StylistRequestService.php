
<?php

namespace App\Services;

use App\Models\StylistRequest;

class StylistRequestService
{
    public function createRequest($validatedData)
    {
        $stylistRequest = StylistRequest::create($validatedData);
        return $stylistRequest->id; // Assuming 'id' is the primary key and unique identifier
    }
}
