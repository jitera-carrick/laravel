<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest; // Import the new form request validation class
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Str;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // New method to create a hair stylist request
    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        // Authenticate the user based on the "user_id"
        $userId = $request->input('user_id');
        $user = User::find($userId);
        if (!$user || $userId != Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // The CreateHairStylistRequest form request class handles the validation
        $validatedData = $request->validated();

        // Create a new HairStylistRequest model instance
        $hairStylistRequest = new HairStylistRequest([
            'user_id' => $user->id,
            'area' => $validatedData['area'],
            'menu' => $validatedData['menu'],
            'hair_concerns' => $validatedData['hair_concerns'],
            'status' => 'pending', // Set the initial status to 'pending'
        ]);

        // Save the new request to the database
        $hairStylistRequest->save();

        // Iterate over the "image_paths" array and create RequestImage instances
        if (isset($validatedData['image_paths'])) {
            foreach ($validatedData['image_paths'] as $imagePath) {
                $requestImage = new RequestImage([
                    'request_id' => $hairStylistRequest->id,
                    'image_path' => $imagePath,
                ]);
                $requestImage->save();
            }
        }

        // If images are provided, iterate over the 'images' array and create new instances of RequestImage
        if (isset($validatedData['images'])) {
            foreach ($validatedData['images'] as $image) {
                // Store the image and create a RequestImage instance
                $filename = Str::random(10) . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('request_images', $filename, 'public');
                $requestImage = new RequestImage([
                    'request_id' => $hairStylistRequest->id,
                    'image_path' => $path,
                ]);
                $requestImage->save();
            }
        }

        // Prepare the response data
        $responseData = [
            'request_id' => $hairStylistRequest->id,
            'area' => $hairStylistRequest->area,
            'menu' => $hairStylistRequest->menu,
            'hair_concerns' => $hairStylistRequest->hair_concerns,
            'status' => $hairStylistRequest->status,
            'image_paths' => $hairStylistRequest->requestImages()->pluck('image_path'),
            'created_at' => $hairStylistRequest->created_at->toDateTimeString(),
            'updated_at' => $hairStylistRequest->updated_at->toDateTimeString(),
        ];

        // Return the response with the newly created request details
        return response()->json($responseData);
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
