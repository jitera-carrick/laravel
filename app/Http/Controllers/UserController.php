<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\VerifyEmailNotification;
use Carbon\Carbon;

class UserController extends Controller
{
    // ... other methods ...

    public function updateProfile(UpdateUserProfileRequest $request)
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
}
