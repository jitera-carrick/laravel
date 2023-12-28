<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\UpdateHairStylistRequest; // Import the new request validation class
use App\Models\User;
use App\Models\Request as HairStylistRequest; // Renamed to avoid confusion with HTTP Request
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

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
                // The email validation rule is updated to ignore the user's own email
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

    public function updateHairStylistRequest(UpdateHairStylistRequest $request): JsonResponse
    {
        // Validate that the "user_id" corresponds to a logged-in customer
        if ($request->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.',
            ], 403);
        }

        // Validate that the "request_id" exists and belongs to the user
        $hairStylistRequest = HairStylistRequest::where('user_id', $request->user_id)
            ->findOrFail($request->request_id);

        // If the "area" field is provided, update the "area" field
        if ($request->filled('area')) {
            $hairStylistRequest->area = $request->area;
        }

        // If the "menu" field is provided, update the "menu" field
        if ($request->filled('menu')) {
            $hairStylistRequest->menu = $request->menu;
        }

        // If the "hair_concerns" field is provided, validate its length and update
        if ($request->filled('hair_concerns')) {
            $hairStylistRequest->hair_concerns = $request->hair_concerns;
        }

        // If the "image_paths" array is provided, validate and update the "request_images" table
        if ($request->filled('image_paths')) {
            // Assuming there is a method in HairStylistRequest model to handle image updates
            $hairStylistRequest->updateImages($request->image_paths);
        }

        // Save the updated request
        $hairStylistRequest->save();

        // Return a success message
        return response()->json([
            'success' => true,
            'request_id' => $hairStylistRequest->id,
            'message' => 'Hair stylist request updated successfully.'
        ], 200);
    }
}
