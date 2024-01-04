<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request as HttpRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    // ... other methods ...
    
    // Method to create a hair stylist request
    public function createHairStylistRequest(HttpRequest $httpRequest): JsonResponse
    {
        // ... existing code for createHairStylistRequest method ...
    }

    // Method to update a hair stylist request
    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        // ... existing code for update method ...
    }

    // Method to delete an image from a hair stylist request
    public function deleteImage($requestId, $imageId): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $stylistRequest = Request::where('id', $requestId)->where('user_id', $user->id)->first();

        if (!$stylistRequest) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        $requestImage = RequestImage::where('id', $imageId)->where('request_id', $requestId)->first();

        if (!$requestImage) {
            return response()->json(['message' => 'Image not found or does not belong to the specified request.'], 404);
        }

        try {
            // Delete the image file from storage
            if (Storage::disk('public')->exists($requestImage->image_path)) {
                Storage::disk('public')->delete($requestImage->image_path);
            }
            // Delete the image record from the database
            $requestImage->delete();
            return response()->json(['status' => 204], 204);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete the image.'], 500);
        }
    }

    // ... other methods ...
}
