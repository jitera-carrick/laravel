<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use App\Models\Area;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to update a hair stylist request
    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        // ... existing update method code ...
    }

    // Method to delete a request image
    public function deleteRequestImage($request_id, $image_id): JsonResponse
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $hairRequest = Request::find($request_id);
        if (!$hairRequest) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        $requestImage = RequestImage::where('id', $image_id)->where('request_id', $request_id)->first();

        if (!$requestImage) {
            return response()->json(['message' => 'Image not found or does not belong to the request.'], 404);
        }

        if ($requestImage->delete()) {
            // Assuming the image is stored in the public disk, delete it from the storage
            Storage::disk('public')->delete($requestImage->image_path);

            return response()->json(['status' => 200, 'message' => 'Image successfully deleted.'], 200);
        }

        return response()->json(['message' => 'Failed to delete the image.'], 500);
    }

    // ... other methods ...
}
