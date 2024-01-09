
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

        // Ensure 'service_details' is not empty
        if (empty($data['service_details'])) {
            throw new \Exception('Service details cannot be empty');
        }

        // Validate 'preferred_date' and 'preferred_time'
        $currentDate = new \DateTime();
        $preferredDate = new \DateTime($data['preferred_date']);
        if ($preferredDate <= $currentDate) {
            throw new \Exception('Preferred date cannot be in the past');
        }

        // Set the initial status of the request
        $data['status'] = 'pending';

        // If a request_image_id is provided, verify it
        if (!empty($data['request_image_id'])) {
            $requestImage = RequestImage::find($data['request_image_id']);
            if (!$requestImage) {
                throw new \Exception('Invalid request_image_id provided');
            }
        } else {
            $data['request_image_id'] = null;
        }

        // Create a new HairStylistRequest record
        $hairStylistRequest = HairStylistRequest::create($data);

        return $hairStylistRequest;
    } // End of createRequest method

    public function sendStylistRequest(int $userId): HairStylistRequest
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('Invalid user_id provided');
        }

        $hairStylistRequest = new HairStylistRequest([
            'user_id' => $userId,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $hairStylistRequest->save();

        return $hairStylistRequest;
    }

    public function createStylistRequest(int $userId): HairStylistRequest
    {
        $user = User::find($userId);
        if (!$user) {
            throw new \Exception('Invalid user_id provided');
        }

        $hairStylistRequest = new HairStylistRequest([
            'user_id' => $userId,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $hairStylistRequest->save();

        return $hairStylistRequest;
    }
} // End of HairStylistRequestService class
