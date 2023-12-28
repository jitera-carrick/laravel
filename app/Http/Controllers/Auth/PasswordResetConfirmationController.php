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
            'token' => 'required|string',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }

        // Find the token
        $passwordResetToken = PasswordResetToken::where('token', $request->token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$passwordResetToken) {
            return response()->json(['message' => 'Invalid or expired password reset token.'], 404);
        }

        // Find the user and reset the password
        $user = $passwordResetToken->user;
        $user->password = Hash::make($request->password);
        $user->save();

        // Mark the token as used
        $passwordResetToken->used = true;
        $passwordResetToken->save();

        return response()->json(['status' => 200, 'message' => 'Password has been reset successfully.'], 200);
    }
}
