<?php

namespace App\Http\Controllers;

use App\Models\RequestImage;
use App\Models\HairStylistRequest; // Added to check if request_id exists
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class HairStylistRequestImageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function destroy($request_id, $image_id): JsonResponse
    {
        // Check if the request_id exists in the database
        $hairStylistRequest = HairStylistRequest::find($request_id);
        if (!$hairStylistRequest) {
            return response()->json(['message' => 'Request not found.'], 404); // Changed status code to 404
        }

        // Check if the image_id exists in the database and is associated with the given request_id
        $requestImage = RequestImage::where('request_id', $request_id)
                                    ->where('id', $image_id)
                                    ->first();

        if (!$requestImage) {
            return response()->json(['message' => 'Image not found.'], 404); // Changed status code to 404
        }

        $requestImage->delete();

        return response()->json(['status' => 'success', 'message' => 'Image deleted successfully.'], 200);
    }
}
