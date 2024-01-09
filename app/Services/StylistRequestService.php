
<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\RequestImage;
use App\Models\StylistRequest;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Validator;

class StylistRequestService
{
    // ... existing methods ...

    /**
     * Create a new stylist request.
     *
     * @param array $validatedData
     * @return HairStylistRequest
     * @throws Exception
     */
    public function create($validatedData)
    {
        $validatedData['status'] = 'pending';
        $hairStylistRequest = HairStylistRequest::create($validatedData);

        return $hairStylistRequest;
    }

    public function updateStylistRequest($request_id, $validatedData)
    {
        $validator = Validator::make($validatedData, [
            'id' => 'required|exists:stylist_requests,id',
            'preferred_date' => 'required|date',
            'preferred_time' => 'required|date_format:H:i',
            'stylist_preferences' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }

        $stylistRequest = StylistRequest::find($request_id);

        if (!$stylistRequest) {
            throw new Exception('Stylist request not found.');
        }

        if ($stylistRequest->user_id !== $validatedData['user_id']) {
            throw new Exception('Unauthorized access to the request.');
        }

        $stylistRequest->update([
            'preferred_date' => $validatedData['preferred_date'],
            'preferred_time' => $validatedData['preferred_time'],
            'stylist_preferences' => $validatedData['stylist_preferences'],
        ]);

        return $stylistRequest;
    }

    // ... rest of the existing methods ...
}
