<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

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

        // Check if the new password and password confirmation match
        if ($request->password !== $request->password_confirmation) {
            return response()->json(['message' => 'Password confirmation does not match.'], 400);
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
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Begin transaction to ensure atomicity
        DB::beginTransaction();
        try {
            $user->password = Hash::make($request->password);
            $user->password_reset_required = false; // Set to false as per requirement
            $user->save();

            // Invalidate the used token by deleting it
            $passwordResetToken->delete(); // Changed from marking as used to deleting

            DB::commit();
            return response()->json(['status' => 200, 'message' => 'Password has been reset successfully.'], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }
}
