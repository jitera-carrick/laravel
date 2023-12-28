<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\User;
use App\Models\Request as HairRequest; // Renamed to avoid conflict with HttpRequest
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request as HttpRequest; // Import the HttpRequest
use Illuminate\Support\Facades\DB; // Import the DB facade

class UserController extends Controller
{
    // ... other methods ...

    public function updateProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        // Existing updateProfile method code...
    }

    public function updateUserProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        // Existing updateUserProfile method code...
    }

    // New method for creating a hair stylist request
    public function createHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Ensure the user is authenticated and the user_id matches the authenticated user's ID
        if (Auth::id() !== $request->user_id) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Validate the request data
        $validatedData = $request->validate([
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'nullable|string|max:3000',
            'image_paths' => 'nullable|array|max:3',
            'image_paths.*' => 'image|mimes:png,jpg,jpeg|max:5120', // 5MB
        ]);

        // Begin transaction to ensure atomicity
        try {
            DB::beginTransaction();

            // Create a new HairRequest model instance
            $hairRequest = new HairRequest([
                'user_id' => Auth::id(),
                'area' => $validatedData['area'],
                'menu' => $validatedData['menu'],
                'hair_concerns' => $validatedData['hair_concerns'] ?? '',
                'status' => 'pending', // Default status
            ]);

            // Save the HairRequest model
            $hairRequest->save();

            // Iterate over the image paths and create RequestImage instances
            if (!empty($validatedData['image_paths'])) {
                foreach ($validatedData['image_paths'] as $imagePath) {
                    $requestImage = new RequestImage([
                        'request_id' => $hairRequest->id,
                        'image_path' => $imagePath,
                    ]);
                    $requestImage->save();
                }
            }

            DB::commit();

            // Return a success response
            return response()->json([
                'success' => true,
                'request_id' => $hairRequest->id,
                'message' => 'Hair stylist request created successfully.',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create hair stylist request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
