<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest; // Import the new form request validation class
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // New method to create a hair stylist request
    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        // Authenticate the user based on the "user_id"
        // ... existing code ...
    }

    // New method to create a request
    public function createRequest(HttpRequest $httpRequest): JsonResponse
    {
        // Ensure the user is authenticated
        // ... existing code ...
    }

    // Updated method to cancel a hair stylist request
    public function cancelHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Validate the request_id and check if it exists in the requests table
        // ... existing code ...
    }

    // New method to delete a request image
    public function deleteRequestImage($requestId, $imageId): JsonResponse
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Retrieve the request and image models
        $hairStylistRequest = HairStylistRequest::where('user_id', Auth::id())->find($requestId);
        if (!$hairStylistRequest) {
            return response()->json(['message' => 'Request not found or you do not have permission to edit this request.'], 403);
        }

        $requestImage = RequestImage::where('request_id', $hairStylistRequest->id)->find($imageId);
        if (!$requestImage) {
            return response()->json(['message' => 'Image not found or you do not have permission to delete this image.'], 403);
        }

        // Delete the image
        $requestImage->delete();

        // Return success response
        return response()->json([
            'status' => 200,
            'message' => 'Image has been successfully deleted.'
        ]);
    }

    // ... other methods ...
}
