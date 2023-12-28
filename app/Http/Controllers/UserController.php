<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\UpdateHairStylistRequest; // Import the UpdateHairStylistRequest
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

    // New method to update a hair stylist request
    public function updateHairStylistRequest(UpdateHairStylistRequest $request, $id): JsonResponse
    {
        $hairStylistRequest = HairStylistRequest::find($id);

        if (!$hairStylistRequest) {
            return response()->json(['message' => 'Request not found.'], 404);
        }

        if ($hairStylistRequest->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized access.'], 401);
        }

        $hairStylistRequest->fill($request->validated());

        // Handle file uploads for images if provided
        if ($request->hasFile('images')) {
            $allowedFileTypes = ['png', 'jpg', 'jpeg'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            $files = $request->file('images');

            if (count($files) > 3) {
                return response()->json(['message' => 'You can only upload up to 3 images.'], 400);
            }

            foreach ($files as $file) {
                if (!$file->isValid() || !in_array($file->getClientOriginalExtension(), $allowedFileTypes) || $file->getSize() > $maxFileSize) {
                    return response()->json(['message' => 'Invalid image format or file size too large.'], 400);
                }

                // Assuming RequestImage is a model that handles the storage of images
                // and has a relationship with HairStylistRequest
                $requestImage = new RequestImage();
                $requestImage->path = $file->store('images'); // This will store the image and return the path
                $hairStylistRequest->images()->save($requestImage);
            }
        }

        $hairStylistRequest->save();

        return response()->json([
            'status' => 200,
            'request' => $hairStylistRequest
        ]);
    }

    // ... other methods ...
}
