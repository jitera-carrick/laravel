
<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\User;
use App\Models\RequestImage;

class HairStylistRequestService
{
    // This method has been updated to createHairStylistRequest and now only handles the creation logic
    public function createHairStylistRequest($validatedData)
    {
        // Create a new HairStylistRequest model instance with the provided data
        $hairStylistRequest = new HairStylistRequest($validatedData);

        // Save the new hair stylist request to the database
        $hairStylistRequest->save();

        // Return the ID of the newly created hair stylist request
        return $hairStylistRequest->id;
    }

    public function createRequest($data)
    {
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

        // Create a new HairStylistRequest record with the provided data
        $hairStylistRequest = HairStylistRequest::create($data);

        return $hairStylistRequest;
    }
}
