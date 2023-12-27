<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\PasswordReset;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordConfirmationMail;
use App\Services\PasswordPolicyService; // Assuming this service exists as per the guideline

class ResetPasswordController extends Controller
{
    // Add your existing methods here

    // Method to handle password reset
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
        $passwordPolicyService = new PasswordPolicyService();
        $passwordPolicy = $passwordPolicyService->getPasswordPolicy();

        // Validation rules
        $rules = [
            'new_password' => [
                'required',
                'string',
                'min:' . $passwordPolicy->minimum_length, // Use dynamic minimum length from password policy
                'confirmed',
                function ($attribute, $value, $fail) use ($passwordPolicyService, $passwordPolicy) {
                    $errors = $passwordPolicyService->validatePassword($value, $passwordPolicy);
                    if (!empty($errors)) {
                        foreach ($errors as $error) {
                            $fail($error);
                        }
                    }
                },
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/', // Updated regex to ensure a mix of letters and numbers
            ],
        ];

        // Custom error messages
        $messages = [
            'new_password.min' => 'Password must be ' . $passwordPolicy->minimum_length . ' characters or more.', // Custom message for minimum length
            'new_password.regex' => 'Password must contain a mix of letters and numbers.', // Custom message for regex
        ];

        // Validate request
        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Additional password validations
        $password = $request->input('new_password');
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

        // Send a confirmation email to the user's email address
        // This line is added to ensure the confirmation email is sent as per the existing code
        Mail::to($user->email)->send(new ResetPasswordConfirmationMail($user));

        // Return a success response
        return response()->json(['message' => 'Your password has been successfully reset.'], 200);
    }

    // Method to validate reset token
    public function validateResetToken(Request $request)
    {
        $token = $request->input('reset_token');

        if (empty($token)) {
            return response()->json(['valid' => false, 'message' => 'Reset token is required.'], 400);
        }

        $passwordReset = PasswordReset::where('token', $token)->first();

        if (!$passwordReset) {
            return response()->json(['valid' => false, 'message' => 'The reset token does not exist.'], 404);
        }

        if ($passwordReset->expires_at < now()) {
            return response()->json(['valid' => false, 'message' => 'The reset token has expired.'], 410);
        }

        return response()->json(['valid' => true, 'message' => 'The reset token is valid.'], 200);
    }

    // Existing methods...
}
