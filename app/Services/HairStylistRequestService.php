<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\User;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;

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

    public function cancelRequest(int $request_id)
    {
        $hairStylistRequest = HairStylistRequest::find($request_id);

        if (!$hairStylistRequest) {
            throw new \Exception('Request not found.');
        }

        if ($hairStylistRequest->user_id !== Auth::id()) {
            throw new \Exception('User is not authorized to cancel this request.');
        }

        $hairStylistRequest->status = 'canceled';
        $hairStylistRequest->save();

        return 'Request canceled successfully.';
    }
}
