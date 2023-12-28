<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
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

    public function expireRequest(int $request_id): JsonResponse
    {
        try {
            $request = HairStylistRequest::findOrFail($request_id);

            // Assuming there is a method to check if the treatment plan's date and time have passed
            // This is a placeholder for the actual logic that would determine if the request is expired
            if (Carbon::now()->greaterThan($request->created_at->addDays(30))) { // Example condition
                $request->status = 'expired';
                $request->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Request has been successfully expired.'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Request expiration date has not passed yet.'
                ], 400);
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while expiring the request.'
            ], 500);
        }
    }

    public function deleteRequestImage(int $request_id, int $image_id): JsonResponse
    {
        try {
            $request = HairStylistRequest::findOrFail($request_id);
            $image = $request->requestImages()->findOrFail($image_id);

            // Delete the image
            $image->delete();

            // Update the request's updated_at timestamp
            $request->touch();

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully.',
                'request_id' => $request->id
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Image or request not found.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the image.'
            ], 500);
        }
    }
}
