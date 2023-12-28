<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

    public function createHairStylistRequest(HttpRequest $httpRequest): JsonResponse
    {
        // Ensure the user_id corresponds to the currently authenticated user
        $user = Auth::user();
        if ($httpRequest->user_id != $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        // Validate the incoming request
        $validator = Validator::make($httpRequest->all(), [
            'area' => 'required|string',
            'menu' => 'required|string',
            'hair_concerns' => 'nullable|string|max:3000',
            'image_paths' => 'nullable|array|max:3',
            'image_paths.*' => 'required_with:image_paths|image|mimes:png,jpg,jpeg|max:5120', // Custom validation rules would be added here
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()], 422);
        }

        // Create a new Request model instance
        $request = new Request([
            'user_id' => $user->id,
            'area' => $httpRequest->area,
            'menu' => $httpRequest->menu,
            'hair_concerns' => $httpRequest->hair_concerns,
            'status' => 'pending',
        ]);

        // Save the Request model instance to the database
        $request->save();

        // Iterate over the image_paths array and create RequestImage model instances
        if ($httpRequest->has('image_paths')) {
            foreach ($httpRequest->image_paths as $image_path) {
                // Assuming the image_path is a valid uploaded file instance
                $filename = Str::random(10) . '.' . $image_path->getClientOriginalExtension();
                $image_path->storeAs('request_images', $filename, 'public');

                $requestImage = new RequestImage([
                    'request_id' => $request->id,
                    'image_path' => 'request_images/' . $filename,
                ]);
                $requestImage->save();
            }
        }

        // Return a JSON response with a success message
        return response()->json([
            'success' => true,
            'message' => 'Hair stylist request created successfully.',
            'request_id' => $request->id,
        ], 201);
    }

    // ... existing methods ...
}
