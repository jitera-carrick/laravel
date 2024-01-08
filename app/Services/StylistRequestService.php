
<?php

namespace App\Services;

use App\Models\Request;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\StylistRequest;

class StylistRequestService
{
    public function createRequest($validatedData)
    {
        // Create a new request entry in the database
        $request = new Request([
            'details' => $validatedData['details'],
            'status' => $validatedData['status'] ?? 'pending',
            'user_id' => $validatedData['user_id'],
            'hair_concerns' => $validatedData['hair_concerns'],
            'priority' => $validatedData['priority'],
            'stylist_request_id' => $stylistRequest->id,
        ]);
        $request->save();

        // If "area" and "menu" are provided, create new entries and associate them with the request
        if (isset($validatedData['area'])) {
            $requestArea = new RequestArea([
                'area_name' => $validatedData['area'],
                'request_id' => $request->id,
            ]);
            $requestArea->save();
        }

        if (isset($validatedData['menu'])) {
            $requestMenu = new RequestMenu([
                'menu_name' => $validatedData['menu'],
                'request_id' => $request->id,
            ]);
            $requestMenu->save();
        }

        return $request;
        return $stylistRequest->id; // Assuming 'id' is the primary key and unique identifier
    }
}
