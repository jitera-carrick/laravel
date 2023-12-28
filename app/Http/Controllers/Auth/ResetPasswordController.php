<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ResetPasswordController extends Controller
{
    // ... (other methods in the controller)

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
     * Verify the password reset token.
     *
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyPasswordResetToken($token)
    {
        if (empty($token)) {
            return response()->json(['message' => 'Invalid token.'], 400);
        }

        $tokenRecord = PasswordResetToken::where('token', $token)->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        if ($tokenRecord->used) {
            return response()->json(['message' => 'This password reset token has already been used.'], 400);
        }

        if (Carbon::parse($tokenRecord->expires_at)->isPast()) {
            return response()->json(['message' => 'This password reset token has expired.'], 400);
        }

        return response()->json(['status' => 200, 'message' => 'The token is valid.']);
    }

    // ... (rest of the code in the controller)
}
