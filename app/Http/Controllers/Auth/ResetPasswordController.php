<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPasswordNotification;

class ResetPasswordController extends Controller
{
    // Add the reset method below

    public function reset(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|confirmed|min:' . Config::get('auth.password_length'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Retrieve the token entry
        $passwordResetToken = PasswordResetToken::where('email', $request->email)
            ->where('token', $request->token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$passwordResetToken) {
            return response()->json(['message' => 'Invalid token or email.'], 404);
        }

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Update the user's password
        $user->password = Hash::make($request->password);
        $user->password_reset_required = false;
        $user->save();

        // Mark the token as used
        $passwordResetToken->used = true;
        $passwordResetToken->save();

        // Dispatch the reset password notification
        Notification::send($user, new ResetPasswordNotification());

        return response()->json(['message' => 'Password has been successfully reset.'], 200);
    }

    // Existing methods...
}
