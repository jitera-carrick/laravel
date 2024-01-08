
<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\Request;
use App\Models\RequestImage;
use Exception;

class RequestService
{
    public function deleteRequestImage($request_id, $image_id)
    {
        // Update the method to accept request_image_id as the only parameter
        public function deleteRequestImage($request_image_id)
        {
            // Use RequestImage model to find the image by request_image_id
            $image = RequestImage::find($request_image_id);
            if (!$image) {
                throw new Exception("Image not found.");
            }

            // Check if the image is associated with any hair stylist request
            $hairStylistRequest = HairStylistRequest::where('request_image_id', $request_image_id)->first();
            if ($hairStylistRequest) {
                $hairStylistRequest->request_image_id = null;
                $hairStylistRequest->save();
            }

            // Delete the image record
            $image->delete();
            return "Image has been successfully deleted.";
        }

        $image->delete();
        return true;
    }
}
