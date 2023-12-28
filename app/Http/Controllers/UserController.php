<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest; // Import the new form request validation class
use App\Http\Requests\UpdateRequest; // Import the UpdateRequest form request validation class
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // New method to create a hair stylist request
    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        // ... existing code for createHairStylistRequest method ...
    }

    // Updated method to cancel a hair stylist request
    public function cancelHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing code for cancelHairStylistRequest method ...
    }

    // New method to edit a hair stylist request
    public function editRequest(UpdateRequest $request): JsonResponse
    {
        // Authenticate the user and ensure they are logged in
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Retrieve the request using the Request model and the provided request_id
        $hairStylistRequest = HairStylistRequest::where('id', $request->input('request_id'))
                                                 ->where('user_id', $user->id)
                                                 ->first();

        // Ensure that the authenticated user has permission to edit the request
        if (!$hairStylistRequest) {
            return response()->json(['message' => 'Request not found or access denied.'], 403);
        }

        // Validate the request data
        $validator = Validator::make($request->all(), [
            'area' => 'sometimes|string',
            'menu' => 'sometimes|string',
            'hair_concerns' => 'sometimes|string',
            'image_paths.*' => 'sometimes|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update the request with the new values if provided
        $validatedData = $validator->validated();
        $hasUpdates = false;
        foreach (['area', 'menu', 'hair_concerns'] as $field) {
            if (array_key_exists($field, $validatedData)) {
                $hairStylistRequest->$field = $validatedData[$field];
                $hasUpdates = true;
            }
        }

        // Save the request if there were any updates
        if ($hasUpdates) {
            $hairStylistRequest->save();
        }

        // Handle the image_path updates
        if ($request->has('image_paths')) {
            foreach ($request->file('image_paths') as $image) {
                // Store the image and create a new RequestImage instance
                $path = $image->store('request_images', 'public');
                $requestImage = new RequestImage([
                    'request_id' => $hairStylistRequest->id,
                    'image_path' => $path,
                ]);
                $requestImage->save();
            }
        }

        // Handle image deletions
        if ($request->has('deleted_image_ids')) {
            foreach ($request->input('deleted_image_ids') as $imageId) {
                $image = RequestImage::find($imageId);
                if ($image && $image->request_id === $hairStylistRequest->id) {
                    // Delete the image file
                    Storage::disk('public')->delete($image->image_path);
                    // Delete the image record
                    $image->delete();
                }
            }
        }

        // Return a response with a confirmation message upon successful update
        return response()->json([
            'message' => 'Request updated successfully.',
            'request_id' => $hairStylistRequest->id,
            'area' => $hairStylistRequest->area,
            'menu' => $hairStylistRequest->menu,
            'hair_concerns' => $hairStylistRequest->hair_concerns,
            'image_paths' => $hairStylistRequest->requestImages()->pluck('image_path'),
        ]);
    }

    // ... other methods ...
}
