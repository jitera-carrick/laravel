<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PasswordResetRequiredNotification;

class UserController extends Controller
{
    // ... (other methods in the UserController)

    /**
     * Enforce a password reset for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function enforcePasswordReset($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password_reset_required = true;
        $user->save();

        // Optionally, invalidate all existing sessions for the user
        Auth::logoutOtherDevices($user->password);

        // Send a notification to the user
        Notification::send($user, new PasswordResetRequiredNotification());

        return response()->json(['message' => 'Password reset enforcement successful']);
    }

    /**
     * Enforce a password reset on the user's next login.
     *
     * @param  Request $request
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function enforcePasswordResetOnNextLogin(Request $request, $userId)
    {
        // Validate the user_id parameter
        if (!is_numeric($userId)) {
            return response()->json(['message' => 'Invalid user ID.'], 400);
        }

        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password_reset_required = true;
        $user->save();

        // Optionally, invalidate all existing sessions for the user
        Auth::logoutOtherDevices($user->password);

        return response()->json(['status' => 200, 'message' => 'User must reset password on next login.']);
    }

    // ... (rest of the UserController code)
}
