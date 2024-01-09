
<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\User;
use App\Models\RequestImage;

class HairStylistRequestService
{
    public function createRequest($data)
    {
        // Check if the user_id corresponds to a valid user
        $user = User::find($data['user_id']);
        if (!$user) {
            throw new \Exception('Invalid user_id provided');
        }

        // If a request_image_id is provided, verify it
        if (isset($data['request_image_id'])) {
            $requestImage = RequestImage::find($data['request_image_id']);
            if (!$requestImage) {
                throw new \Exception('Invalid request_image_id provided');
            }
        }

        // Create a new HairStylistRequest record
        $hairStylistRequest = HairStylistRequest::create($data);

        $hairStylistRequest->status = 'pending';
        $hairStylistRequest->created_at = now();
        $hairStylistRequest->updated_at = now();
        $hairStylistRequest->save();

        return $hairStylistRequest;
    }

    public function createHairStylistRequest($validatedData)
    {
        // Check if the user_id corresponds to a valid user
        $user = User::find($validatedData['user_id']);
        if (!$user) {
            throw new \Exception('Invalid user_id provided');
        }

        // Set the status to 'pending' and create the HairStylistRequest
        $validatedData['status'] = 'pending';
        $hairStylistRequest = HairStylistRequest::create($validatedData);
        $hairStylistRequest->created_at = now();
        $hairStylistRequest->updated_at = now();

        return $hairStylistRequest;
    }
}
