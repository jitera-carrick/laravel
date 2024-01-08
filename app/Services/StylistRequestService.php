
<?php

namespace App\Services;

use App\Models\StylistRequest;
use App\Models\Request;
use App\Models\RequestImage;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\DB;

class StylistRequestService
{
    public function createRequest($validatedData)
    {
        $stylistRequest = StylistRequest::create($validatedData);
        return $stylistRequest->id; // Assuming 'id' is the primary key and unique identifier
    }

    public function deleteImage($request_id, $image_id)
    {
        try {
            $request = Request::findOrFail($request_id);
            $image = $request->requestImages()->where('id', $image_id)->firstOrFail();

            if ($image->delete()) {
                return $image_id;
            }

            throw new CustomException('Unable to delete the image.');
        } catch (\Exception $e) {
            throw new CustomException('Error occurred: ' . $e->getMessage());
        }
    }
}
