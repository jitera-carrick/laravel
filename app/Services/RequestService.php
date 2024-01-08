
<?php

namespace App\Services;

use App\Models\Request;
use App\Models\RequestImage;
use Exception;

class RequestService
{
    public function deleteRequestImage($request_id, $image_id)
    {
        $request = Request::find($request_id);
        if (!$request) {
            throw new Exception("Request not found.");
        }

        $image = RequestImage::where('request_id', $request_id)->where('id', $image_id)->first();
        if (!$image) {
            throw new Exception("Image not found or does not belong to the request.");
        }

        $image->delete();
        return true;
    }
}
