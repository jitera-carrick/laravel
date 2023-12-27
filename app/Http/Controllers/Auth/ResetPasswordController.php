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
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    // Add your existing methods here

    // New method to handle password reset
    public function reset(Request $request, $token) // Updated method signature to include $token
    {
        // Validate the token is not blank
        if (empty($token)) {
            return response()->json(['message' => 'Token is required.'], 400);
        }

        // Find the password reset token using the provided token instead of request data
        $passwordReset = PasswordReset::where('token', $token)->first();
        if (!$passwordReset || $passwordReset->expires_at < now()) {
            return response()->json(['message' => 'The token does not exist or has expired.'], 404);
        }

        // Retrieve password policy
        $passwordPolicy = PasswordPolicy::firstOrFail();

        // Validation rules
        $rules = [
            'password' => [
                'required',
                'string',
                'min:6', // Updated minimum length to 6 as per requirement
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/', // Updated regex to ensure a mix of letters and numbers
            ],
        ];

        // Custom error messages
        $messages = [
            'password.min' => 'Password must be 6 digits or more.', // Custom message for minimum length
            'password.regex' => 'Password must contain a mix of letters and numbers.', // Custom message for regex
        ];

        // Validate request
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Additional password validations
        $password = $request->input('password');
        $user = User::where('email', $passwordReset->email)->first();

        if (Str::contains($password, $user->email)) {
            return response()->json(['message' => 'Password cannot be the same as the email address.'], 400);
        }

        // Update the user's password
        $user->password = Hash::make($password);
        $user->save();

        // Update the status in the "password_resets" table to indicate the password has been reset
        $passwordReset->status = 'completed';
        $passwordReset->save();

        // Delete the password reset token
        $passwordReset->delete();

        // Trigger a ResetPasswordNotification
        Notification::send($user, new ResetPasswordNotification()); // Use Notification facade as in the existing code

        // Return a success response
        return response()->json(['message' => 'Your password has been successfully reset.'], 200);
    }

    // Existing methods...
}
