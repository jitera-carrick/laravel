<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestAreaSelection;
use App\Models\RequestMenuSelection;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // New method to create a hair stylist request
    public function createHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Existing code for createHairStylistRequest method
        // ...
    }

    // Updated method to cancel a hair stylist request
    public function cancelHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Existing code for cancelHairStylistRequest method
        // ...
    }

    /**
     * Delete an image from a hair stylist request.
     *
     * @param  HttpRequest $request
     * @param  int  $request_id
     * @param  int  $image_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteHairStylistRequestImage(HttpRequest $request, $request_id, $image_id): JsonResponse
    {
        // Validate the request_id and image_id to ensure they are numbers
        if (!is_numeric($request_id) || !is_numeric($image_id)) {
            return response()->json(['message' => 'Invalid ID format.'], 400);
        }

        // Ensure the user is authenticated
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        try {
            $hairStylistRequest = HairStylistRequest::findOrFail($request_id);
            $image = RequestImage::where('request_id', $hairStylistRequest->id)
                                 ->where('id', $image_id)
                                 ->first();

            if (!$image) {
                return response()->json(['message' => 'Image not found.'], 404);
            }

            // Check if the authenticated user is the owner of the request
            if ($hairStylistRequest->user_id != Auth::id()) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            $image->delete();

            return response()->json(['message' => 'Image successfully deleted.'], 200);
        } catch (\Exception $e) {
            // Log the exception and return a 500 error response
            \Log::error($e->getMessage());
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }

    // ... other methods ...
}
