<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateHairStylistRequest; // Import the new form request validation class
use App\Http\Requests\UpdateHairStylistRequest; // Import the UpdateHairStylistRequest form request validation class
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

    /**
     * Update an existing hair stylist request.
     *
     * @param UpdateHairStylistRequest $request
     * @param HairStylistRequest $hairStylistRequest
     * @return JsonResponse
     */
    public function updateHairStylistRequest(UpdateHairStylistRequest $request, HairStylistRequest $hairStylistRequest): JsonResponse
    {
        // The UpdateHairStylistRequest class handles the validation and authorization
        $validatedData = $request->validated();

        // Check if the authenticated user is the owner of the request
        if ($hairStylistRequest->user_id != Auth::id()) {
            return response()->json(['message' => 'Request not found or you do not have permission to edit this request.'], 403);
        }

        // Update the request with the validated data
        $hairStylistRequest->fill([
            'area' => $validatedData['area'],
            'menu' => $validatedData['menu'],
            'hair_concerns' => $validatedData['hair_concerns'],
        ]);
        $hairStylistRequest->save();

        // Prepare the response data
        $responseData = [
            'id' => $hairStylistRequest->id,
            'area' => $hairStylistRequest->area,
            'menu' => $hairStylistRequest->menu,
            'hair_concerns' => $hairStylistRequest->hair_concerns,
            'status' => $hairStylistRequest->status,
            'updated_at' => $hairStylistRequest->updated_at->toDateTimeString(),
            'user_id' => $hairStylistRequest->user_id,
        ];

        // Return the response with the updated request details
        return response()->json(['status' => 200, 'request' => $responseData]);
    }

    // ... other methods ...
}
