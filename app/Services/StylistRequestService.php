<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\RequestImage;
use App\Models\StylistRequest;
use App\Models\User;
use Exception;

class StylistRequestService
{
    public function createHairStylistRequest($details, $status, $user_id, $request_image_id = null)
    {
        if ($request_image_id && !RequestImage::find($request_image_id)) {
            throw new Exception('Invalid request_image_id provided.');
        }

        $hairStylistRequest = new HairStylistRequest([
            'details' => $details,
            'status' => $status,
            'user_id' => $user_id,
            'request_image_id' => $request_image_id,
        ]);

        $hairStylistRequest->save();

        return $hairStylistRequest;
    }

    public function updateHairStylistRequest($request_id, $details, $status, $user_id, $request_image_id = null)
    {
        $hairStylistRequest = HairStylistRequest::find($request_id);

        if (!$hairStylistRequest || $hairStylistRequest->user_id !== $user_id) {
            throw new Exception('Invalid request_id or user_id provided.');
        }

        if ($request_image_id && !RequestImage::find($request_image_id)) {
            throw new Exception('Invalid request_image_id provided.');
        }

        $hairStylistRequest->update([
            'details' => $details,
            'status' => $status,
            'request_image_id' => $request_image_id,
        ]);

        return $hairStylistRequest;
    }

    public function createStylistRequest($validatedData)
    {
        // Check if the user_id exists
        if (!User::find($validatedData['user_id'])) {
            throw new Exception('User not found.');
        }

        // Set the status to 'pending' before creating the request
        $validatedData['status'] = 'pending';
        $stylistRequest = StylistRequest::create($validatedData);
        return $stylistRequest->id; // Assuming 'id' is the primary key and unique identifier
    }

    public function cancelRequest(int $userId, int $requestId)
    {
        // Correct the model used to find the request from HairStylistRequest to StylistRequest
        $request = StylistRequest::where('id', $requestId)->where('user_id', $userId)->first();

        if (!$request) {
            return false;
        }

        $request->status = 'canceled'; // Update the status to "canceled"
        $request->updated_at = now(); // Update the "updated_at" timestamp
        $request->save();

        return [
            'message' => 'Request canceled successfully.',
            'request_id' => $requestId,
        ];
    }

    public function updateStylistRequest($validatedData)
    {
        $stylistRequest = StylistRequest::find($validatedData['id']);

        if (!$stylistRequest || $stylistRequest->user_id !== $validatedData['user_id']) {
            throw new Exception('Invalid request_id or user_id provided.');
        }

        $stylistRequest->fill($validatedData);
        $stylistRequest->updated_at = now();
        $stylistRequest->save();

        return $stylistRequest;
    }

    // Other methods of the service class...
}
