<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest; // Import the new form request validation class
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;

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
        foreach ($validatedData['image_paths'] as $imagePath) {
            $requestImage = new RequestImage([
                'request_id' => $hairStylistRequest->id,
                'image_path' => $imagePath,
            ]);
            $requestImage->save();
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

    // New method to delete a request image
    public function deleteRequestImage(HttpRequest $request): JsonResponse
    {
        // Validate the image_id and check if it exists in the request_images table
        $validatedData = $request->validate([
            'image_id' => 'required|exists:request_images,id',
        ]);

        // Use the RequestImage model to find the image by image_id
        $requestImage = RequestImage::find($validatedData['image_id']);

        // If the image does not exist, return a response with an error message and a 404 status code
        if (!$requestImage) {
            return response()->json(['message' => 'Image not found.'], 404);
        }

        // Implement authorization logic to verify that the authenticated user has permission to delete the image
        $hairStylistRequest = HairStylistRequest::find($requestImage->request_id);
        if (!$hairStylistRequest || $hairStylistRequest->user_id != Auth::id()) {
            // If the user is not authorized, return a response with an error message and a 403 status code
            return response()->json(['message' => 'Unauthorized to delete this image.'], 403);
        }

        // If the image exists and the user is authorized, delete the image record from the request_images table
        $requestImage->delete();

        // Return a response with a confirmation message indicating that the image has been successfully deleted
        return response()->json(['message' => 'Image has been successfully deleted.'], 200);
    }

    // ... other methods ...
}
