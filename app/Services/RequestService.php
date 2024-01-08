
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

    public function cancelStylistRequest($id, $user_id)
    {
        $request = Request::where('id', $id)->where('user_id', $user_id)->first();
        if (!$request) {
            throw new Exception("Request not found or does not belong to the user.");
        }

        if ($request->status !== 'pending') {
            throw new Exception("Request is not in a cancellable state.");
        }

        $request->status = 'cancelled';
        $request->updated_at = now();
        $request->save();

        return $request;
    }
}
