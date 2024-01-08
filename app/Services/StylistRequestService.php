
<?php

namespace App\Services;

use App\Models\Request;
use App\Models\StylistRequest;

class StylistRequestService
{
    public function createRequest($validatedData)
    {
        $stylistRequest = StylistRequest::create($validatedData);
        return $stylistRequest->id; // Assuming 'id' is the primary key and unique identifier
        // Create a new Request entry
        $request = Request::create([
            'area' => $validatedData['area'],
            'menu' => $validatedData['menu'],
            'hair_concerns' => $validatedData['hair_concerns'],
            'status' => 'pending',
            'priority' => $validatedData['priority'],
            'user_id' => $validatedData['user_id'],
        ]);

        // Link the StylistRequest to the newly created Request
        $stylistRequest->request_id = $request->id;
        $stylistRequest->status = 'pending';
        $stylistRequest->save();

        // Update the status of the Request
        $request->status = 'linked';
        $request->save();

        return "Stylist request created successfully with request ID: " . $request->id;
}
