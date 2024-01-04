
<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\CreateHairStylistRequest; // Import the new form request validation class
use App\Http\Requests\UpdateHairStylistRequest; // Import the update form request validation class
use App\Http\Requests\ValidateStylistRequest; // Import the ValidateStylistRequest form request validation class
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response; // Import the Response facade
use Illuminate\Support\Facades\Notification; // Added import for Notification facade
use Illuminate\Support\Str;
use App\Models\StylistRequest;
use App\Events\StylistRequestSubmitted;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // Method to create or update a hair stylist request
    public function createOrUpdateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing code ...
    }

    // Method to cancel a hair stylist request
    public function cancelHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing code ...
    }

    // Method to update a hair stylist request
    public function updateHairStylistRequest(HttpRequest $request, $id): JsonResponse
    {
        // ... existing code ...
    }

    /**
     * Delete a specific image from a hair stylist request.
     *
     * @param int $request_id The ID of the hair stylist request.
     * @param int $image_id The ID of the image to delete.
     * @return JsonResponse
     */
    public function deleteRequestImage(int $request_id, int $image_id): JsonResponse
    {
        // ... existing code ...
    }

    /**
     * Submit a stylist request.
     *
     * @param HttpRequest $request
     * @return JsonResponse
     */
    public function submitStylistRequest(HttpRequest $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            if ($request->input('user_id') != $userId) {
                return response()->json(['message' => 'Unauthorized.'], 401);
            }

            $requestTime = now();
            $stylistRequest = new StylistRequest([
                'user_id' => $userId,
                'request_time' => $requestTime,
                'status' => 'pending',
            ]);
            $stylistRequest->save();

            // Notification logic here (assuming Notification class exists)
            // Notification::send($admins, new StylistRequestReceived($stylistRequest));

            return response()->json(['request_id' => $stylistRequest->id, 'request_time' => $requestTime->toDateTimeString()]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while submitting the stylist request.'], 500);
        }
    }

    // ... other methods ...
}
