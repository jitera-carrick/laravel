<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class ResetPasswordController extends Controller
{
    // ... (other methods)

    /**
     * Handle the password reset confirmation flow.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        // Validate the input ensuring all fields are present and "password" matches "password_confirmation".
        $validatedData = $request->validated();

        // Check the format of the "email" using Laravel's built-in validation rules to ensure it is a valid email address.
        // This is already handled by the ResetPasswordRequest validation.

        // Query the PasswordResetToken model to find a token that matches the provided "token" and is associated with the "email".
        $passwordResetToken = PasswordResetToken::where('token', $validatedData['token'])
            ->where('email', $validatedData['email'])
            ->first();

        // Handle the case where no matching token is found or the token has expired.
        if (!$passwordResetToken || $passwordResetToken->created_at->addMinutes(config('auth.passwords.users.expire')) < now()) {
            return response()->json(['message' => 'This password reset token is invalid or has expired.'], 422);
        }

        // Use the User model to find the user with the matching "email".
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Encrypt the new "password" using Laravel's Hash facade to ensure secure password hashing.
        $user->password = Hash::make($validatedData['password']);

        // Update the user's "password" in the database with the new encrypted password and save the changes.
        $user->save();

        // Invalidate the used password reset token by deleting it from the database.
        $passwordResetToken->delete();

        // Return a JSON response with a success message indicating that the password has been reset successfully.
        return response()->json(['message' => 'Your password has been reset successfully.']);
    }

    // ... (other methods)
}
