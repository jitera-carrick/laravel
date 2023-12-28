<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\CreateHairStylistRequest;
use App\Models\User;
use App\Models\Request;
use App\Models\RequestImage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    // ... other methods ...

    public function updateProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        // Retrieve the user with the given ID instead of using Auth::user()
        $user = User::findOrFail($request->user_id);

        // Check if the current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        // Validate and update the new password
        if ($request->filled('new_password')) {
            // Check if new password matches the confirmation
            if ($request->new_password === $request->new_password_confirmation) {
                $user->password = Hash::make($request->new_password);
            } else {
                return response()->json(['message' => 'New password confirmation does not match.'], 422);
            }
        }

        // Validate and update the email if it has changed
        $emailChanged = false;
        if ($request->filled('email') && $request->email !== $user->email) {
            $request->validate([
                'email' => 'required|email|unique:users,email',
            ]);
            $user->email = $request->email;
            $user->email_verified_at = null;
            $emailChanged = true;
        }

        // Update the updated_at timestamp
        $user->updated_at = Carbon::now();

        // Save the user's updated information
        $user->save();

        // If the email was changed, send a verification notification
        if ($emailChanged) {
            Notification::send($user, new VerifyEmailNotification());
        }

        return response()->json(['message' => 'Profile updated successfully.'], 200);
    }

    public function updateUserProfile(UpdateUserProfileRequest $request): JsonResponse
    {
        $user = Auth::user();

        // Validate the request parameters
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->errors(),
            ], 422);
        }

        // Update user profile
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->updated_at = Carbon::now();

        $user->save();

        // Return the updated user profile
        return response()->json([
            'status' => 200,
            'message' => 'Profile updated successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'updated_at' => $user->updated_at->toIso8601String(),
            ]
        ], 200);
    }

    public function createHairStylistRequest(CreateHairStylistRequest $request): JsonResponse
    {
        // Ensure that the user_id corresponds to a logged-in customer
        if ($request->user_id != Auth::id()) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        // Validate "area" and "menu" fields
        $validator = Validator::make($request->all(), [
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'sometimes|string|max:3000',
            'image_paths.*' => 'sometimes|file|image|max:5120|mimes:png,jpg,jpeg',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 422);
        }

        // Create a new entry in the requests table using the validated data
        $hairStylistRequest = Request::create([
            'user_id' => $request->user_id,
            'area' => $request->area,
            'menu' => $request->menu,
            'hair_concerns' => $request->hair_concerns,
            'status' => 'pending', // Default status
        ]);

        // If image_paths are provided, iterate over each path and create new entries in the request_images table
        if ($request->has('image_paths')) {
            $images = $request->file('image_paths');
            if (count($images) > 3) {
                return response()->json(['message' => 'You can only upload up to 3 images.'], 422);
            }

            foreach ($images as $image) {
                $path = $image->store('request_images', 'public'); // Assuming 'public' disk is configured
                RequestImage::create([
                    'image_path' => $path,
                    'request_id' => $hairStylistRequest->id,
                ]);
            }
        }

        // Return a success response with the request ID, status, and a confirmation message
        return response()->json([
            'status' => 200,
            'message' => 'Hair stylist request created successfully.',
            'request' => [
                'id' => $hairStylistRequest->id,
                'status' => $hairStylistRequest->status,
            ]
        ], 200);
    }
}
