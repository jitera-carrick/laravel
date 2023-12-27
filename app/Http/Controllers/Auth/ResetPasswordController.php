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

    // New method to handle password reset
    public function reset(Request $request, $token) // Updated method signature to include $token
    {
        // Existing reset method code...
    }

    // New method to validate reset token
    public function validateResetToken(Request $request)
    {
        // Existing validateResetToken method code...
    }

    // New method to set new password
    public function setNewPassword(Request $request)
    {
        $token = $request->input('token');
        $newPassword = $request->input('password');

        // Token validation
        if (empty($token)) {
            return response()->json(['message' => 'Token is required.'], 400);
        }

        $passwordReset = PasswordReset::where('token', $token)->first();
        if (!$passwordReset || $passwordReset->expires_at < now()) {
            return response()->json(['message' => 'The token is invalid or has expired.'], 400);
        }

        // Password validation
        $passwordPolicyService = new PasswordPolicyService();
        $passwordPolicy = $passwordPolicyService->getPasswordPolicy();

        $rules = [
            'password' => [
                'required',
                'string',
                'min:' . $passwordPolicy->minimum_length,
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
                function ($attribute, $value, $fail) use ($passwordPolicyService, $passwordPolicy) {
                    $errors = $passwordPolicyService->validatePassword($value, $passwordPolicy);
                    if (!empty($errors)) {
                        foreach ($errors as $error) {
                            $fail($error);
                        }
                    }
                },
            ],
        ];

        $messages = [
            'password.min' => 'Password must be ' . $passwordPolicy->minimum_length . ' characters or more.',
            'password.regex' => 'Password must contain a mix of letters and numbers.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if ($newPassword === $user->email) {
            return response()->json(['message' => 'Password must be different from the email address.'], 400);
        }

        // Update password
        $user->password = Hash::make($newPassword);
        $user->save();

        // Delete the password reset token
        $passwordReset->delete();

        // Trigger a ResetPasswordNotification
        Notification::send($user, new ResetPasswordNotification());

        // Send a confirmation email to the user's email address
        Mail::to($user->email)->send(new ResetPasswordConfirmationMail($user));

        return response()->json(['message' => 'Your password has been updated successfully.'], 200);
    }

    // Existing methods...
}
