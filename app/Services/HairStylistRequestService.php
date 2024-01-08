
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

        return $hairStylistRequest;
    }

    public function deleteImagesByHairStylistRequestId(int $hairStylistRequestId)
    {
        $hairStylistRequest = HairStylistRequest::find($hairStylistRequestId);
        if (!$hairStylistRequest) {
            throw new \Exception('Hair stylist request not found');
        }

        $images = $hairStylistRequest->requestImages;
        if ($images->isEmpty()) {
            throw new \Exception('No images found for the specified hair stylist request');
        }

        foreach ($images as $image) {
            $image->delete(); // Soft delete the image
        }
    }

    // Other methods in the HairStylistRequestService class...

}
