<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Models\HairStylistRequest;
use App\Models\User;
use App\Models\RequestImage;
use Illuminate\Support\Facades\DB;

class HairStylistRequestService
{
    public function createOrUpdateRequest(array $data)
    {
        // Check if the user_id corresponds to a valid user
        if (isset($data['user_id'])) {
            $user = User::find($data['user_id']);
            if (!$user) {
                throw new \Exception('The user does not exist.');
            }
        }

        // If a request_image_id is provided, verify it
        if (isset($data['request_image_id'])) {
            $requestImage = RequestImage::find($data['request_image_id']);
            if (!$requestImage) {
                throw new \Exception('Invalid request_image_id provided');
            }
        }

        // Check if the status is valid
        if (isset($data['status']) && !StatusEnum::isValid($data['status'])) {
            throw new \Exception('Invalid status value.');
        }

        // Create or update the HairStylistRequest
        if (isset($data['id'])) {
            $hairStylistRequest = HairStylistRequest::findOrFail($data['id']);

            if ($hairStylistRequest->user_id !== $data['user_id']) {
                throw new \Exception('User ID does not match request owner.');
            }

            $hairStylistRequest->update($data);
        } else {
            $hairStylistRequest = new HairStylistRequest($data);
            $hairStylistRequest->save();
        }

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

    public function deleteImageByPath($hairStylistRequestId, $imagePath)
    {
        $requestImage = RequestImage::where('hair_stylist_request_id', $hairStylistRequestId)
                                    ->where('image_path', $imagePath)
                                    ->first();

        if (!$requestImage) {
            throw new \Exception('Image not found or does not belong to the specified hair stylist request');
        }

        if (!$requestImage->delete()) {
            throw new \Exception('Failed to delete the image');
        }
    }

    // Other methods in the HairStylistRequestService class...
}
