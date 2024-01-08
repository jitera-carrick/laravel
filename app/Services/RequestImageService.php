
<?php

namespace App\Services;

use App\Models\RequestImage;
use App\Models\HairStylistRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class RequestImageService
{
    public function deleteImage($id)
    {
        $requestImage = RequestImage::find($id);
        if (!$requestImage) {
            throw new Exception("Image not found");
        }

        $hairStylistRequest = HairStylistRequest::where('request_image_id', $id)->first();
        if ($hairStylistRequest) {
            $hairStylistRequest->request_image_id = null;
            $hairStylistRequest->save();
        }

        if (Storage::disk('public')->exists($requestImage->image_path)) {
            Storage::disk('public')->delete($requestImage->image_path);
        }

        $requestImage->delete();

        Log::info("Request image deleted", ['id' => $id]);

        return true;
    }
}
