<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB; // Add this line to use DB transactions
use Illuminate\Support\Facades\Storage; // Add this line to use Storage facade for file operations

class UserController extends Controller
{
    // ... other methods ...

    // Existing methods remain unchanged

    // New method to create a hair stylist request
    public function createHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // ... existing code for createHairStylistRequest method ...
    }

    // New method to edit a hair stylist request
    public function editHairStylistRequest(HttpRequest $request): JsonResponse
    {
        // Validate that the "user_id" corresponds to a logged-in customer.
        $userId = Auth::id();
        $requestId = $request->route('request_id'); // Assuming the request_id is passed as a route parameter

        // Retrieve the existing request
        $hairStylistRequest = HairStylistRequest::where('user_id', $userId)
                                                 ->where('id', $requestId)
                                                 ->first();

        if (!$hairStylistRequest) {
            return response()->json(['message' => 'Request not found or does not belong to the user.'], 404);
        }

        // Check if the request's status allows for editing
        if (!in_array($hairStylistRequest->status, ['pending', 'open'])) {
            return response()->json(['message' => 'Request cannot be edited in its current status.'], 403);
        }

        // Validate the request data
        $validatedData = $request->validate([
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'nullable|string|max:3000',
            'image_paths.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max size
        ]);

        // Check the number of images
        if (isset($validatedData['image_paths']) && count($validatedData['image_paths']) > 3) {
            throw ValidationException::withMessages([
                'image_paths' => 'No more than three images can be uploaded.',
            ]);
        }

        // Start a transaction
        DB::beginTransaction();
        try {
            // Update the request's fields
            $hairStylistRequest->area = $validatedData['area'];
            $hairStylistRequest->menu = $validatedData['menu'];
            $hairStylistRequest->hair_concerns = $validatedData['hair_concerns'];
            $hairStylistRequest->save();

            // Remove existing images
            $existingImages = RequestImage::where('request_id', $hairStylistRequest->id)->get();
            foreach ($existingImages as $existingImage) {
                Storage::disk('public')->delete($existingImage->image_path);
                $existingImage->delete();
            }

            // Save new images
            if (isset($validatedData['image_paths'])) {
                foreach ($validatedData['image_paths'] as $image) {
                    $requestImage = new RequestImage([
                        'image_path' => $image->store('request_images', 'public'), // Store the image and get the path
                        'request_id' => $hairStylistRequest->id,
                    ]);
                    $requestImage->save();
                }
            }

            // Commit the transaction
            DB::commit();

            // Return a JSON response with the updated request details
            return response()->json([
                'request_id' => $hairStylistRequest->id,
                'status' => $hairStylistRequest->status,
                'area' => $hairStylistRequest->area,
                'menu' => $hairStylistRequest->menu,
                'hair_concerns' => $hairStylistRequest->hair_concerns,
                'image_paths' => $hairStylistRequest->requestImages()->get()->pluck('image_path'),
                'updated_at' => $hairStylistRequest->updated_at,
            ], 200);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();
            return response()->json(['message' => 'Failed to update the request.'], 500);
        }
    }

    // ... other methods ...
}
