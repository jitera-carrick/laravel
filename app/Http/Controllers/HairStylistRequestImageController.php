<?php

namespace App\Http\Controllers;

use App\Models\RequestImage;
use App\Models\HairStylistRequest; // Assuming this is the model for hair stylist requests
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth facade for authentication check

class HairStylistRequestImageController extends Controller
{
    // ... other methods ...

    // New method to delete a hair stylist request image
    public function deleteImage($request_id, $image_id): JsonResponse
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Validate that $request_id and $image_id are numeric
        if (!is_numeric($request_id) || !is_numeric($image_id)) {
            return response()->json(['message' => 'Invalid ID format.'], 400);
        }

        // Check if the request exists in the database
        $hairStylistRequest = HairStylistRequest::find($request_id);
        if (!$hairStylistRequest) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        // Check if the image exists in the database with the given request_id and image_id
        $requestImage = RequestImage::where('request_id', $request_id)
                                    ->where('id', $image_id)
                                    ->first();

        if (!$requestImage) {
            return response()->json(['message' => 'Image not found.'], 404);
        }

        // Delete the image from the database
        $requestImage->delete();

        // Return a success response
        return response()->json(['message' => 'Image successfully deleted.'], 200);
    }

    // ... other methods ...
}
