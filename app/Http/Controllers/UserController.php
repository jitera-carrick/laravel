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

    // New method to create a request
    public function createRequest(HttpRequest $httpRequest): JsonResponse
    {
        // Ensure the user is authenticated
        $userId = $httpRequest->input('user_id');
        $user = User::find($userId);
        if (!$user || $userId != Auth::id()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Validate the input fields
        $validator = Validator::make($httpRequest->all(), [
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'nullable|string|max:3000',
            'image_path' => 'nullable|array',
            'image_path.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $validatedData = $validator->validated();

        // Check if the user has an existing request that has not passed a certain date and time
        $existingRequest = HairStylistRequest::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('created_at', '>', now()->subDays(30)) // Example: Check if the request was created within the last 30 days
            ->first();

        if ($existingRequest) {
            return response()->json(['message' => 'You already have a pending request.'], 422);
        }

        // Create a new request
        $request = new HairStylistRequest([
            'user_id' => $user->id,
            'area' => $validatedData['area'],
            'menu' => $validatedData['menu'],
            'hair_concerns' => $validatedData['hair_concerns'] ?? '',
            'status' => 'pending',
        ]);
        $request->save();

        // Create request images if provided
        if (isset($validatedData['image_path'])) {
            foreach ($validatedData['image_path'] as $image) {
                $requestImage = new RequestImage([
                    'request_id' => $request->id,
                    'image_path' => $image->store('request_images', 'public'), // Example: Store the image in the 'public/request_images' directory
                ]);
                $requestImage->save();
            }
        }

        // Return a JSON response with a confirmation message
        return response()->json([
            'message' => 'Request successfully registered.',
            'request_id' => $request->id,
        ]);
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
