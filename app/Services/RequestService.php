
<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\RequestImage;
use Exception;

class RequestService
{
    public function deleteRequestImage($request_image_id)
    {
        // Use RequestImage model to find the image by request_image_id
        $image = RequestImage::find($request_image_id);
        if (!$image) {
            throw new Exception("Image not found.");
        }

        // Check if the image is associated with any HairStylistRequest
        $hairStylistRequest = $image->hairStylistRequest;
        if ($hairStylistRequest) {
            $hairStylistRequest->request_image_id = null;
            $hairStylistRequest->save();
        }

        // Instead of deleting the image, perform a soft delete
        $image->softDelete();
        return "Image has been successfully deleted.";
    }
}
?>
