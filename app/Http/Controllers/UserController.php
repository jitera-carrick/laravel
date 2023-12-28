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

    // Updated method to create or update a hair stylist request
    public function createHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Authenticate the user based on the "user_id"
        $userId = $request->input('user_id');
        $user = User::find($userId);
        if (!$user || $userId != Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Validate "area" and "menu" selections are not empty
        $validator = Validator::make($request->all(), [
            'area' => 'required',
            'menu' => 'required',
            'hair_concerns' => 'required|max:3000',
            'image_paths' => 'required|array',
            'image_paths.*' => 'image|mimes:png,jpg,jpeg|max:5120', // 5MB in kilobytes
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

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
            'status' => $hairStylistRequest->status,
            'message' => 'Hair stylist request registration has been successfully processed.'
        ];

        // Return the response with the newly created or updated request details
        return response()->json($responseData);
    }

    // ... other methods ...
}
