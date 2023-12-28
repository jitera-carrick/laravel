<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Http\Requests\EditHairStylistRequest; // New request class for editing hair stylist request
use App\Models\User;
use App\Models\Request; // Import the Request model
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator; // Import the Validator facade
use Illuminate\Support\Facades\DB; // Import the DB facade for transactions
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

    // New method for editing hair stylist request
    public function editHairStylistRequest(EditHairStylistRequest $request): JsonResponse
    {
        $user = Auth::user();
        $requestId = $request->route('request_id'); // Assuming the request_id is passed as a route parameter

        // Use Eloquent's findOrFail to retrieve the request and ensure it belongs to the authenticated user
        $hairStylistRequest = Request::where('user_id', $user->id)->findOrFail($requestId);

        // Begin transaction
        DB::beginTransaction();
        try {
            // For updating `area` and `menu`
            if ($request->filled('area')) {
                $hairStylistRequest->area = $request->input('area');
            }
            if ($request->filled('menu')) {
                $hairStylistRequest->menu = $request->input('menu');
            }

            // For the `hair_concerns` field
            if ($request->filled('hair_concerns')) {
                if (strlen($request->input('hair_concerns')) > 3000) {
                    return response()->json(['message' => 'Hair concerns may not be greater than 3000 characters.'], 422);
                }
                $hairStylistRequest->hair_concerns = $request->input('hair_concerns');
            }

            // Handling `image_paths`
            if ($request->has('image_paths')) {
                // Delete old images
                $hairStylistRequest->requestImages()->delete();

                // Insert new images
                foreach ($request->image_paths as $imagePath) {
                    $hairStylistRequest->requestImages()->create(['image_path' => $imagePath]);
                }
            }

            // Update the updated_at timestamp
            $hairStylistRequest->updated_at = Carbon::now();

            // Save the request's updated information
            $hairStylistRequest->save();

            // Commit the transaction
            DB::commit();

            // Return a JsonResponse with the request ID and a success message
            return response()->json([
                'status' => 200,
                'message' => 'Request updated successfully.',
                'request_id' => $hairStylistRequest->id,
            ], 200);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollback();
            return response()->json(['message' => 'Failed to update the request.'], 500);
        }
    }
}
