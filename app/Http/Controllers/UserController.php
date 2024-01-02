<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest; // Import the new form request validation class
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestArea;
use App\Models\RequestMenu;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // Method to create a hair stylist request
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
            'area' => 'required|exists:request_areas,id',
            'menu' => 'required|exists:request_menus,id',
            'hair_concerns' => 'required|max:3000',
            'image_path' => 'required|image|mimes:png,jpg,jpeg|max:5120', // 5MB in kilobytes
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        // Check if the authenticated user has an existing request that can be edited
        $existingRequest = HairStylistRequest::where('user_id', $user->id)
                                             ->where('status', 'pending')
                                             ->first();

        if ($existingRequest) {
            // Update the existing request with the new data
            $existingRequest->update([
                'hair_concerns' => $validatedData['hair_concerns'],
            ]);
            $hairStylistRequest = $existingRequest;
        } else {
            // Create a new HairStylistRequest model instance
            $hairStylistRequest = new HairStylistRequest([
                'user_id' => $user->id,
                'hair_concerns' => $validatedData['hair_concerns'],
                'status' => 'pending', // Set the initial status to 'pending'
            ]);

            // Save the new request to the database
            $hairStylistRequest->save();
        }

        // Insert the selected 'area' into the request_areas table
        $requestArea = new RequestArea([
            'request_id' => $hairStylistRequest->id,
            'area_id' => $validatedData['area'],
        ]);
        $requestArea->save();

        // Insert the selected 'menu' into the request_menus table
        $requestMenu = new RequestMenu([
            'request_id' => $hairStylistRequest->id,
            'menu_id' => $validatedData['menu'],
        ]);
        $requestMenu->save();

        // Save the 'image_path' into the 'request_images' table
        $requestImage = new RequestImage([
            'request_id' => $hairStylistRequest->id,
            'image_path' => $validatedData['image_path'],
        ]);
        $requestImage->save();

        // Update the 'created_at' and 'updated_at' timestamps for the new request and associated images
        $hairStylistRequest->touch();
        $requestImage->touch();

        // Prepare the response data
        $responseData = [
            'request_id' => $hairStylistRequest->id,
            'status' => $hairStylistRequest->status,
            'area_selection' => $requestArea->area_id,
            'menu_selection' => $requestMenu->menu_id,
            'hair_concerns' => $hairStylistRequest->hair_concerns,
            'image_path' => $requestImage->image_path,
            'message' => 'Hair stylist request has been successfully created.'
        ];

        // Return the response with the newly created request details
        return response()->json($responseData);
    }

    // ... other methods ...

    // Method to create or update a hair stylist request
    public function createOrUpdateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Existing code remains unchanged
        // ...
    }

    // ... other methods ...

    // Method to cancel a hair stylist request
    public function cancelHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Existing code remains unchanged
        // ...
    }

    // ... other methods ...
}
