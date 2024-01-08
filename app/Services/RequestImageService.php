
<?php

namespace App\Services;

use App\Models\RequestImage;
use Exception;

class RequestImageService
{
    public function deleteRequestImage(int $requestImageId): bool
    {
        $image = RequestImage::find($requestImageId);
        if (!$image) {
            throw new Exception("Image not found.");
        }
        $image->delete();
        return true;
    }
}
