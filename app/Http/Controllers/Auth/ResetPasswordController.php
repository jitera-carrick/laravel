<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\PasswordReset;
use App\Models\PasswordResetToken; // Import the PasswordResetToken model
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordConfirmationMail;
use App\Services\PasswordPolicyService; // Assuming this service exists as per the guideline
use App\Exceptions\InvalidEmailException; // Import the InvalidEmailException
use Illuminate\Http\JsonResponse; // Import JsonResponse
use Illuminate\Support\Facades\Log; // Import Log facade for logging exceptions

class ResetPasswordController extends Controller
{
    // Add your existing methods here

    // Method to handle password reset
    public function reset(Request $request, $token = null)
    {
        // Use the token from the request if not provided as a parameter
        $token = $token ?? $request->input('token');

        // Validate the token is not blank
        if (empty($token)) {
            return response()->json(['message' => 'Token is required.'], 400);
        }

        // Find the password reset token using the provided token instead of request data
        $passwordResetModel = class_exists(PasswordResetToken::class) ? new PasswordResetToken() : new PasswordReset();
        $passwordReset = $passwordResetModel->where('token', $token)->first();
        if (!$passwordReset || $passwordReset->expires_at < now()) {
            return response()->json(['message' => 'The token does not exist or has expired.'], 404);
        }

        // Validate the email is present and valid
        $email = $request->input('email');
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Invalid or missing email address.'], 422);
        }

        // Retrieve password policy
        $passwordPolicyService = new PasswordPolicyService();
        $passwordPolicy = $passwordPolicyService->getPasswordPolicy();

        // Validation rules
        $rules = [
            'new_password' => [
                'required',
                'string',
                'min:' . $passwordPolicy->minimum_length,
                'confirmed',
                function ($attribute, $value, $fail) use ($passwordPolicyService, $passwordPolicy) {
                    $errors = $passwordPolicyService->validatePassword($value, $passwordPolicy);
                    if (!empty($errors)) {
                        foreach ($errors as $error) {
                            $fail($error);
                        }
                    }
                },
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
            ],
            'new_password_confirmation' => 'required_with:new_password|same:new_password',
        ];

        // Custom error messages
        $messages = [
            'new_password.min' => 'Password must be ' . $passwordPolicy->minimum_length . ' characters or more.',
            'new_password.regex' => 'Password must contain a mix of letters and numbers.',
            'new_password_confirmation.required_with' => 'Password confirmation is required.',
            'new_password_confirmation.same' => 'Passwords do not match.',
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
        Notification::send($user, new ResetPasswordNotification());

        // Send a confirmation email to the user's email address
        Mail::to($user->email)->send(new ResetPasswordConfirmationMail($user));

        // Return a success response
        return response()->json(['message' => 'Your password has been successfully reset.'], 200);
    }

    // Method to validate reset token
    public function validateResetToken(Request $request, $token = null)
    {
        // Use the token from the request if not provided as a parameter
        $token = $token ?? $request->input('reset_token');

        if (empty($token)) {
            return response()->json(['valid' => false, 'message' => 'Reset token is required.'], 400);
        }

        try {
            // Check if the PasswordResetToken model exists, if not, use the PasswordReset model
            $passwordResetModel = class_exists(PasswordResetToken::class) ? new PasswordResetToken() : new PasswordReset();
            $passwordReset = $passwordResetModel->where('token', $token)->first();

            // Check if the token exists and is not expired
            if (!$passwordReset || $passwordReset->isExpired()) {
                return response()->json(['valid' => false, 'message' => 'The token does not exist or has expired.'], 404);
            }

            // If the token is valid and not expired, return a positive response
            return response()->json(['valid' => true, 'message' => 'The reset token is valid.'], 200);
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error validating reset token: ' . $e->getMessage());

            // Return a generic error message
            return response()->json(['valid' => false, 'message' => 'An error occurred while validating the token.'], 500);
        }
    }

    // Existing methods...
}
