
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

    public function cancelRequest(int $id, int $userId)
    {
        $stylistRequest = StylistRequest::where('id', $id)->where('user_id', $userId)->first();

        if (!$stylistRequest) {
            throw new Exception("Stylist request not found or user mismatch.");
        }

        if ($stylistRequest->status !== 'pending') {
            throw new Exception("Request cannot be cancelled in its current state.");
        }

        $stylistRequest->update([
            'status' => 'cancelled',
            'updated_at' => now(),
        ]);

        return $stylistRequest;
    }
}
