<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest;
use App\Http\Requests\UpdateHairStylistRequest;
use App\Models\User;
use App\Models\Request as HairStylistRequest;
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // Method to create or update a hair stylist request
    // ... existing method code ...

    // Method to cancel a hair stylist request
    // ... existing method code ...

    // Method to update a hair stylist request
    // ... existing method code ...

    /**
     * Delete a specific image from a hair stylist request.
     *
     * @param HttpRequest $request The HTTP request instance.
     * @return JsonResponse
     */
    public function deleteRequestImage(HttpRequest $request): JsonResponse
    {
        $validatedData = $request->validate([
            'request_id' => 'required|exists:requests,id',
            'image_path' => 'required|string',
        ]);

        try {
            // Find the image entry with the given request_id and image_path
            $requestImage = RequestImage::where('request_id', $validatedData['request_id'])
                                        ->where('image_path', $validatedData['image_path'])
                                        ->first();

            // If the entry is found, delete it
            if ($requestImage) {
                $requestImage->delete();
                return response()->json(['message' => 'Image deleted successfully.'], 200);
            }

            // If the image entry is not found, return a 404 response
            return response()->json(['message' => 'Image not found.'], 404);
        } catch (\Exception $e) {
            // Handle any exceptions that occur during the deletion
            return response()->json(['message' => 'An error occurred while deleting the image.', 'error' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...
}
