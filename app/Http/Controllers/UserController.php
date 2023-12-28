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
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // Updated method to create or update a hair stylist request
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

        // Check if the authenticated user has an existing valid request
        $existingRequest = HairStylistRequest::where('user_id', $user->id)
                                             ->where('status', 'pending')
                                             ->first();

        if ($existingRequest) {
            // Update the existing request with the new data
            $existingRequest->update([
                'area' => $validatedData['area'],
                'menu' => $validatedData['menu'],
                'hair_concerns' => $validatedData['hair_concerns'],
            ]);
            $hairStylistRequest = $existingRequest;
        } else {
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
        }

        // Delete old images if updating an existing request
        if ($existingRequest) {
            $existingRequest->requestImages()->delete();
        }

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
            'message' => 'Hair stylist request registration has been successfully processed.'
        ];

        // Return the response with the newly created or updated request details
        return response()->json($responseData);
    }

    // ... other methods ...

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
        $validatedData = $request->validate([
            'image_id' => 'required|exists:request_images,id',
        ]);

        try {
            // Find the RequestImage by image_id
            $requestImage = RequestImage::findOrFail($validatedData['image_id']);

            // Check if the image is associated with a request belonging to the currently authenticated user
            $hairStylistRequest = HairStylistRequest::where('id', $requestImage->request_id)->firstOrFail();
            if ($hairStylistRequest->user_id != Auth::id()) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            // Delete the image
            $requestImage->delete();

            // Return a JSON response confirming the deletion
            return response()->json([
                'image_id' => $validatedData['image_id'],
                'message' => 'Request image has been successfully deleted.'
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Handle the case where the image does not exist or does not belong to the user
            return response()->json(['message' => 'Request image not found or access denied.'], 404);
        } catch (\Exception $e) {
            // Handle any other potential exceptions
            return response()->json(['message' => 'An error occurred while deleting the request image.'], 500);
        }
    }

    // ... other methods ...
}
