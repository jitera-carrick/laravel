<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest; // Import the new form request validation class
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // Method to create a hair stylist request
    public function createHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Existing code remains unchanged
        // ...
    }

    // ... other methods ...

    // Method to create or update a hair stylist request
    public function createOrUpdateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Existing code remains unchanged
        // ...
    }

    // ... other methods ...

    // Method to cancel a hair stylist request
    public function cancelHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Existing code remains unchanged
        // ...
    }

    // ... other methods ...

    // New method to delete a request image
    public function deleteRequestImage($requestId, $imageId): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        try {
            // Find the request by request_id
            $hairStylistRequest = HairStylistRequest::findOrFail($requestId);

            // Check if the authenticated user is the owner of the request
            if ($hairStylistRequest->user_id != Auth::id()) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }

            // Find the image by image_id and ensure it belongs to the request_id
            $requestImage = RequestImage::where('id', $imageId)
                                        ->where('request_id', $requestId)
                                        ->firstOrFail();

            // Delete the image
            $requestImage->delete();

            // Return a 200 response with a success message
            return response()->json(['status' => 200, 'message' => 'Image has been successfully deleted.']);
        } catch (ModelNotFoundException $e) {
            // Return a 404 response if the request or image does not exist or does not belong to the request
            return response()->json(['message' => $e->getModel() === 'App\Models\Request' ? 'Request not found.' : 'Image not found or does not belong to the specified request.'], 404);
        } catch (\Exception $e) {
            // Return a 500 response if an unexpected error occurs
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    // ... other methods ...
}
