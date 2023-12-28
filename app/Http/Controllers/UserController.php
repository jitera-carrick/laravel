<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\User;
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
        // The new code uses 'id' while the existing code uses 'user_id', we need to handle both cases
        $userId = $request->input('id', $request->input('user_id'));
        $user = User::find($userId);

        // If the user does not exist, return a response indicating the user was not found.
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Check if the current password is correct if provided
        if ($request->filled('current_password') && !Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        // Validate and update the new password if provided
        if ($request->filled('password')) {
            // The new code uses 'password_confirmation' while the existing code uses 'new_password_confirmation'
            // We need to handle both cases
            $passwordConfirmation = $request->input('password_confirmation', $request->input('new_password_confirmation'));
            if ($request->password === $passwordConfirmation) {
                $newSalt = str_random(22); // Generate a new salt
                $newPasswordHash = Hash::make($request->password, ['salt' => $newSalt]); // Hash the new password with the new salt
                $user->password_hash = $newPasswordHash;
                $user->password_salt = $newSalt;
                $user->last_password_reset = Carbon::now();
            } else {
                return response()->json(['message' => 'Password confirmation does not match.'], 422);
            }
        }

        // Validate and update the email if it has changed
        $emailChanged = false;
        if ($request->filled('email') && $request->email !== $user->email) {
            $request->validate([
                'email' => 'required|email|unique:users,email,' . $user->id,
            ]);
            $user->email = $request->email;
            $user->email_verified_at = null;
            $emailChanged = true;
        }

        // Update the user's name if provided
        if ($request->filled('name')) {
            $user->name = $request->name;
        }

        // Update the updated_at timestamp
        $user->updated_at = Carbon::now();

        // Save the user's updated information
        $user->save();

        // If the email was changed, send a verification notification
        if ($emailChanged) {
            Notification::send($user, new VerifyEmailNotification());
        }

        // Return a success response with a message indicating that the user profile has been updated successfully
        return response()->json(['message' => 'User profile updated successfully.'], 200);
    }

    // ... other existing methods ...
}
