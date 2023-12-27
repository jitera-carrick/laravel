<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Models\PasswordPolicy;
use App\Models\PasswordReset;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;

class ResetPasswordController extends Controller
{
    // Add your existing methods here

    // New method to handle password reset
    public function reset(Request $request)
    {
        // Validate the token
        $tokenData = PasswordResetToken::where('token', $request->token)
                    ->where('email', $request->email)
                    ->where('expires_at', '>', now())
                    ->first();

        if (!$tokenData) {
            return response()->json(['message' => 'Invalid or expired password reset token.'], 422);
        }

        // Retrieve password policy
        $passwordPolicy = PasswordPolicy::firstOrFail();

        // Validation rules
        $rules = [
            'email' => 'required|email|exists:users,email', // Use 'exists:users,email' to ensure the user exists
            'token' => 'required|exists:password_reset_tokens,token', // Keep the token validation rule from the new code
            'password' => [
                'required',
                'confirmed',
                'min:' . $passwordPolicy->minimum_length,
                $passwordPolicy->require_digits ? 'regex:/[0-9]/' : '',
                $passwordPolicy->require_letters ? 'regex:/[a-zA-Z]/' : '',
                $passwordPolicy->require_special_characters ? 'regex:/[\W]/' : '',
            ],
        ];

        // Custom error messages
        $messages = [
            'password.regex' => 'The password must include the required types of characters.',
        ];

        // Validate request
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Retrieve the user by their email address
        $user = User::where('email', $request->email)->firstOrFail();

        // Encrypt the new password and update the user record
        $user->password = Hash::make($request->password);
        $user->save();

        // Update the status in the "password_resets" table to indicate the password has been reset
        $passwordReset = PasswordReset::where('email', $request->email)
                          ->where('token', $request->token)
                          ->first();
        if ($passwordReset) {
            $passwordReset->status = 'completed';
            $passwordReset->save();
        }

        // Delete the password reset token
        $tokenData->delete();

        // Trigger a ResetPasswordNotification
        Notification::send($user, new ResetPasswordNotification()); // Use Notification facade as in the existing code

        // Return a success response
        return response()->json(['message' => 'Password has been successfully reset.']);
    }

    // New method to validate reset token
    public function validateResetToken($token)
    {
        try {
            $passwordResetToken = PasswordResetToken::where('token', $token)->firstOrFail();

            if ($passwordResetToken->expires_at < Carbon::now()) {
                return response()->json(['message' => 'Token has expired'], 404);
            }

            return response()->json(['message' => 'Token is valid'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Token is invalid'], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while validating the token'], 500);
        }
    }
}