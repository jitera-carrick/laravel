<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PasswordResetRequest;
use Exception;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8', // Add custom rules or modify as per password policy
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 422);
        }

        try {
            // Check if the email exists
            $user = User::where('email', $request->email)->first();
            // The email existence check is now handled by the validator with 'exists:users,email'

            // Check if the token is valid
            $passwordResetRequest = PasswordResetRequest::where('token', $request->token)
                ->where('expires_at', '>', now())
                ->first();

            if (!$passwordResetRequest) {
                return response()->json(['message' => 'This password reset token is invalid or has expired.'], 404);
            }

            // Update the user's password
            $user->password = Hash::make($request->password);
            $user->save();

            // Invalidate the token
            $passwordResetRequest->delete();

            // Always display a message indicating that a password reset email has been sent
            return response()->json(['message' => 'A password reset link has been sent to the provided email if it exists in our system.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }
}
