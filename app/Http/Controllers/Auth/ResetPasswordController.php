<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\User; // Import User model
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash; // Import Hash facade

class ResetPasswordController extends Controller
{
    // ... (other methods in the controller)

    // Add the new method below

    /**
     * Verify the reset password token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyResetToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        $tokenRecord = PasswordResetToken::where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'This password reset token is invalid.'], 404);
        }

        if ($tokenRecord->used) {
            return response()->json(['message' => 'This password reset token has already been used.'], 400);
        }

        if (Carbon::parse($tokenRecord->expires_at)->isPast()) {
            return response()->json(['message' => 'This password reset token has expired.'], 400);
        }

        return response()->json(['message' => 'The password reset token is valid. You can proceed to reset your password.'], 200);
    }

    /**
     * Reset the user's password.
     *
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request, $token)
    {
        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $tokenRecord = PasswordResetToken::where('token', $token)->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Invalid token.'], 404);
        }

        if ($tokenRecord->used || Carbon::parse($tokenRecord->expires_at)->isPast()) {
            return response()->json(['message' => 'Invalid or expired token.'], 400);
        }

        $user = User::where('email', $tokenRecord->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $tokenRecord->used = true;
        $tokenRecord->save();

        return response()->json(['status' => 200, 'message' => 'Your password has been successfully reset.'], 200);
    }

    // ... (rest of the code in the controller)
}
