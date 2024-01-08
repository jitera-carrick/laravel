
<?php

namespace App\Services;

use App\Models\HairStylistRequest;
use App\Models\Request;
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

            // Optionally delete the image file from the server or cloud storage
            if (Storage::disk(config('filesystems.default'))->exists($image->image_path)) {
                Storage::disk(config('filesystems.default'))->delete($image->image_path);
            }

            // Delete the image record
            $image->delete();
            return "Request image has been successfully deleted.";
        }

    }
}
