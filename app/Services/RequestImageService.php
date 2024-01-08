
<?php

namespace App\Services;

use App\Models\RequestImage;

class RequestImageService
{
    public function deleteImageById(int $id): bool
    {
        $image = RequestImage::find($id);
        if ($image) {
            return $image->delete();
        }
        return false;
    }
}
