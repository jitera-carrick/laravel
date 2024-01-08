
<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use App\Models\Request;
use App\Models\RequestImage;
use App\Models\HairStylistRequest;
use Exception;

class RequestService
{
    public function deleteRequestImage($request_id, $image_id)
    {
        // Check if the request exists
        $request = Request::find($request_id);
        if (!$request) {
            throw new Exception("Request not found.");
        }

        // Find the image associated with the request
        $image = RequestImage::where('request_id', $request_id)->where('id', $image_id)->first();
        if (!$image) {
            throw new Exception("Image not found or does not belong to the request.");
        }

        // Check for linked HairStylistRequest and unlink if necessary
        $hairStylistRequest = $image->hairStylistRequest;
        if ($hairStylistRequest) {
            $hairStylistRequest->request_image_id = null;
            $hairStylistRequest->save();
        }

        // Delete the image file from storage
        Storage::delete($image->image_path);

        // Delete the image record
        $image->delete();
        return true;
    }
}
