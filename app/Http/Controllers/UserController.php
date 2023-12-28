<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // New method to create a hair stylist request
    public function createHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Validate that the "user_id" corresponds to a logged-in customer.
        if ($request->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        }

        // Validate the request data
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'nullable|string|max:3000',
            'image_paths.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max size
        ]);

        // Create a new HairStylistRequest instance and fill it with validated data
        $hairStylistRequest = new HairStylistRequest([
            'user_id' => $validatedData['user_id'],
            'area' => $validatedData['area'],
            'menu' => $validatedData['menu'],
            'hair_concerns' => $validatedData['hair_concerns'],
            'status' => 'pending', // Default status
        ]);

        // Save the new request
        $hairStylistRequest->save();

        // Associate area selections with the request
        $hairStylistRequest->requestAreaSelections()->create([
            'area_id' => $validatedData['area'],
        ]);

        // Associate menu selections with the request
        $hairStylistRequest->requestMenuSelections()->create([
            'menu_id' => $validatedData['menu'],
        ]);

        // Save images associated with the request
        if (isset($validatedData['image_paths'])) {
            foreach ($validatedData['image_paths'] as $imagePath) {
                $hairStylistRequest->requestImages()->create([
                    'image_path' => $imagePath,
                ]);
            }
        }

        // Return a JSON response with the request details
        return response()->json([
            'request_id' => $hairStylistRequest->id,
            'status' => $hairStylistRequest->status,
            'area_selections' => $hairStylistRequest->requestAreaSelections,
            'menu_selections' => $hairStylistRequest->requestMenuSelections,
            'hair_concerns' => $hairStylistRequest->hair_concerns,
            'image_paths' => $hairStylistRequest->requestImages,
            'created_at' => $hairStylistRequest->created_at,
        ], 201);
    }

    // New method to update a hair stylist request
    public function updateHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Validate that the "user_id" corresponds to the currently authenticated user
        // and that the "request_id" exists and belongs to that user.
        $user = Auth::user();
        $hairStylistRequest = HairStylistRequest::where('id', $request->request_id)
                                                ->where('user_id', $user->id)
                                                ->first();

        if (!$hairStylistRequest) {
            return response()->json(['message' => 'Request not found or access denied.'], 404);
        }

        // Validate the input data
        $validator = Validator::make($request->all(), [
            'area' => 'sometimes|required|string',
            'menu' => 'sometimes|required|string',
            'hair_concerns' => 'sometimes|nullable|string|max:3000',
            'image_path.*' => 'sometimes|nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max size
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        // Update the request with the validated data
        $hairStylistRequest->fill($validatedData);
        $hairStylistRequest->save();

        // Handle image_path updates
        if (isset($validatedData['image_path'])) {
            // Delete old images
            foreach ($hairStylistRequest->requestImages as $existingImage) {
                Storage::delete($existingImage->image_path);
                $existingImage->delete();
            }

            // Add new images
            foreach ($validatedData['image_path'] as $image) {
                $path = $image->store('request_images', 'public');
                $hairStylistRequest->requestImages()->create([
                    'image_path' => $path,
                ]);
            }
        }

        // Return a success message with the details of the updated request
        return response()->json([
            'message' => 'Request updated successfully.',
            'request' => [
                'area' => $hairStylistRequest->area,
                'menu' => $hairStylistRequest->menu,
                'hair_concerns' => $hairStylistRequest->hair_concerns,
                'image_paths' => $hairStylistRequest->requestImages,
            ],
        ], 200);
    }

    // ... other methods ...
}
