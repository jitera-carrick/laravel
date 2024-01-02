<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateHairStylistRequest;
use App\Http\Requests\CreateHairStylistRequest;
use App\Models\Request;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RequestController extends Controller
{
    // ... other methods ...

    // Method to create a new hair stylist request
    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        // ... createHairStylistRequest method code ...
    }

    // Method to update a hair stylist request
    public function update(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        // ... existing update method code ...
    }

    /**
     * Delete a specific image from a request.
     *
     * @param  int  $requestId
     * @param  string  $imagePath
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteRequestImage($requestId, $imagePath): JsonResponse
    {
        try {
            // Validate that the "request_id" corresponds to an existing request.
            $hairRequest = Request::findOrFail($requestId);

            // Query the "request_images" table to find the entry with the given "request_id" and "image_path".
            $requestImage = RequestImage::where('request_id', $hairRequest->id)
                                        ->where('image_path', $imagePath)
                                        ->first();

            if (!$requestImage) {
                return response()->json(['message' => 'Image not found.'], 404);
            }

            // Delete the image file from storage
            Storage::disk('public')->delete($imagePath);

            // Delete the image entry from the database
            $requestImage->delete();

            // Return a confirmation of the deletion.
            return response()->json(['message' => 'Image deleted successfully.'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Request not found.'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete image.'], 500);
        }
    }

    // ... other methods ...
}
