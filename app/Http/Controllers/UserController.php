<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\CreateHairStylistRequest; // Import the new form request validation class
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
    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        // Authenticate the user based on the "user_id"
        $user = Auth::user();
        if ($user->id != $request->input('user_id')) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Validate the input data using the CreateHairStylistRequest form request validation class
        $validatedData = $request->validated();

        // Check the length of the "hair_concerns" input
        if (strlen($validatedData['hair_concerns']) > 3000) {
            return response()->json(['message' => 'Hair concerns input exceeds maximum length of 3000 characters.'], 422);
        }

        // Validate the "image_paths" array
        $imagePaths = $validatedData['image_paths'];
        if (count($imagePaths) > 3) {
            return response()->json(['message' => 'No more than three images can be uploaded.'], 422);
        }

        foreach ($imagePaths as $imagePath) {
            // Check if the image file is valid (png, jpg, jpeg) and does not exceed the maximum file size of 5MB
            // Assuming 'validateImagePath' is a method that performs these checks
            if (!$this->validateImagePath($imagePath)) {
                return response()->json(['message' => 'Invalid image file.'], 422);
            }
        }

        // Create a new HairStylistRequest model instance and fill it with the validated data
        $hairStylistRequest = new HairStylistRequest([
            'area' => $validatedData['area'],
            'menu' => $validatedData['menu'],
            'hair_concerns' => $validatedData['hair_concerns'],
            'status' => 'pending review', // Set the status to "pending review"
            'user_id' => $user->id, // Associate the request with the authenticated user
        ]);

        // Save the new HairStylistRequest to the database
        $hairStylistRequest->save();

        // Iterate over the "image_paths" array and create a new RequestImage model instance for each path
        foreach ($imagePaths as $imagePath) {
            $requestImage = new RequestImage([
                'image_path' => $imagePath,
                'request_id' => $hairStylistRequest->id, // Associate the image with the newly created request
            ]);

            // Save the new RequestImage to the database
            $requestImage->save();
        }

        // Return a JSON response with the details of the new request
        return response()->json([
            'request_id' => $hairStylistRequest->id,
            'area' => $hairStylistRequest->area,
            'menu' => $hairStylistRequest->menu,
            'hair_concerns' => $hairStylistRequest->hair_concerns,
            'status' => $hairStylistRequest->status,
            'image_paths' => $hairStylistRequest->requestImages()->pluck('image_path'), // Retrieve image paths from the relationship
            'created_at' => $hairStylistRequest->created_at,
            'updated_at' => $hairStylistRequest->updated_at,
        ]);
    }

    // Method to validate image path (assuming this method exists)
    private function validateImagePath($imagePath)
    {
        // Perform validation checks on the image path
        // This is a placeholder for the actual validation logic
        return true;
    }

    // Updated method to cancel a hair stylist request
    public function cancelHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Validate the request_id and check if it exists in the requests table
        $validatedData = $request->validate([
            'request_id' => 'required|exists:requests,id',
        ]);

        // Retrieve the HairStylistRequest model instance using the request_id
        $hairStylistRequest = HairStylistRequest::findOrFail($validatedData['request_id']);

        // Check if the authenticated user is the owner of the request
        if ($hairStylistRequest->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Update the status column of the HairStylistRequest instance to 'canceled'
        $hairStylistRequest->status = 'canceled';

        // Save the changes to the HairStylistRequest instance
        $hairStylistRequest->save();

        // Return a JsonResponse with the request_id, updated status, and a confirmation message
        return response()->json([
            'request_id' => $hairStylistRequest->id,
            'status' => $hairStylistRequest->status,
            'message' => 'Hair stylist request registration has been successfully canceled.'
        ]);
    }

    // ... other methods ...
}
