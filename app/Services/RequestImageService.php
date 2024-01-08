
<?php

namespace App\Services;

use App\Models\RequestImage;
use Illuminate\Support\Facades\Storage;
use Exception;

class RequestImageService
{
    public function deleteImage($request_id, $image_id)
    {
        try {
            $image = RequestImage::where('id', $image_id)->where('request_id', $request_id)->firstOrFail();
            Storage::delete($image->image_path);
            $image->delete();
        } catch (Exception $e) {
            // Handle the exception as needed
            throw $e;
        }
    }
}
