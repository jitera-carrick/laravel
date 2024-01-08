
<?php

namespace App\Services;

use App\Models\Request;
use App\Models\RequestImage;
use Exception;

class RequestImageService
{
    public function deleteImage($request_id, $image_path)
    {
        $image = RequestImage::where('request_id', $request_id)
                             ->where('image_path', $image_path)
                             ->first();
        if (!$image) {
            throw new Exception("Image not found or does not belong to the request.");
        }

        $image->delete();

        $request = Request::find($request_id);
        $request->updated_at = now();
        $request->save();

        return true;
    }
}
