<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordResetConfirmationController extends Controller
{
    public function confirmReset(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        // Find the token and ensure it matches the provided email
        $passwordResetToken = PasswordResetToken::where('token', $request->token)
            ->where('email', $request->email)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$passwordResetToken) {
            return response()->json(['message' => 'Invalid or expired password reset token.'], 404);
        }

        // Find the user by email and reset the password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        if ($user->password_reset_required ?? false) { // Check if the field exists and is true
            $user->password_reset_required = false; // Set to false if the field exists
        }
        $user->save();

        // Invalidate the used token by deleting it
        $passwordResetToken->delete(); // Changed from marking as used to deleting

        return response()->json(['status' => 200, 'message' => 'Password has been reset successfully.'], 200);
    }
}
