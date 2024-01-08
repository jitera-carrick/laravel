
<?php

namespace App\Services;

use App\Models\RequestImage;
use Illuminate\Support\Facades\Storage;

class RequestImageService
{
    public function deleteImage($request_image_id)
    {
        $requestImage = RequestImage::findOrFail($request_image_id);
        $imagePath = $requestImage->image_path;

        if (Storage::disk(config('filesystems.default'))->exists($imagePath)) {
            Storage::disk(config('filesystems.default'))->delete($imagePath);
        }

        $requestImage->delete();
    }
}
