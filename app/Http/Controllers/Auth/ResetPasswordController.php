<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordConfirmationMail;
use App\Services\PasswordPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class ResetPasswordController extends Controller
{
    // Add your existing methods here

    // Method to handle password reset
    public function reset(Request $request, $token = null)
    {
        // Existing reset method code...
    }

    // Method to validate reset token
    public function validateResetToken(Request $request, $token = null)
    {
        // Existing validateResetToken method code...
    }

    // New method to set a new password
    public function setNewPassword(Request $request)
    {
        // Use PasswordPolicyService for password validation
        $passwordPolicyService = new PasswordPolicyService();
        $passwordPolicy = $passwordPolicyService->getPasswordPolicy();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email', // Add email validation
            'password' => array_merge([
                'required',
                'string',
                'confirmed', // Add password confirmation validation
                'min:6', // Ensure password has a minimum length of 6 characters
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/', // Ensure password contains letters and numbers
            ], $passwordPolicyService->getPasswordValidationRules($passwordPolicy)),
            'token' => 'required|string',
        ], [
            'password.confirmed' => 'The password confirmation does not match.', // Add custom error message for password confirmation
            'password.min' => 'Password must be 6 characters or more.', // Custom error message for minimum length
            'password.regex' => 'Password must contain a mix of letters and numbers.', // Custom error message for regex
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $token = $request->input('token');
        $email = $request->input('email');
        $passwordResetToken = PasswordResetToken::where('token', $token)->first();

        if (!$passwordResetToken || $passwordResetToken->expires_at < now()) {
            return response()->json(['message' => 'Token is invalid or has expired.'], 400);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['message' => 'User does not exist.'], 400);
        }

        // Encrypt the new password and update the user's password in the database
        $user->password = Hash::make($request->input('password'));
        $user->save();

        // Set the status of the reset token to indicate it has been used
        $passwordResetToken->status = 'used'; // Set the status to 'used'
        $passwordResetToken->save();

        // Send a confirmation email to the user
        Mail::to($user->email)->send(new ResetPasswordConfirmationMail($user));

        return response()->json(['status' => 200, 'message' => 'Your password has been successfully updated.'], 200);
    }

    // Method to validate the password reset token
    public function validatePasswordResetToken(Request $request, $token = null): JsonResponse
    {
        // Use the token from the request if not provided as a parameter
        $token = $token ?? $request->route('token');

        // Check if the token is provided
        if (empty($token)) {
            return response()->json(['valid' => false, 'message' => 'Token is required.'], 400);
        }

        // Find the password reset token in the database
        $passwordResetToken = PasswordResetToken::where('token', $token)->first();

        // Check if the token exists and is not expired
        if (!$passwordResetToken || $passwordResetToken->isExpired()) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired token.'], 404);
        }

        // Return a success response if the token is valid
        return response()->json(['valid' => true, 'message' => 'Token is valid. You may proceed to set a new password.'], 200);
    }

    // Updated method to handle password reset errors
    public function handlePasswordResetErrors(Request $request)
    {
        $errors = [];

        // Validate email
        if (!$request->filled('email') || !filter_var($request->input('email'), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'The provided email is invalid.';
        }

        // Validate token
        $token = $request->input('token');
        if (empty($token)) {
            $errors['token'] = 'Token is required.';
        } else {
            $passwordResetToken = PasswordResetToken::where('token', $token)->first();
            if (!$passwordResetToken || $passwordResetToken->isExpired()) {
                $errors['token'] = 'The token is invalid or has expired.';
            }
        }

        // Validate password and password confirmation
        $password = $request->input('password');
        $passwordConfirmation = $request->input('password_confirmation');
        if ($password !== $passwordConfirmation) {
            $errors['password_confirmation'] = 'The password confirmation does not match.';
        } else {
            $passwordPolicyService = new PasswordPolicyService();
            $passwordPolicy = $passwordPolicyService->getPasswordPolicy();
            $passwordErrors = $passwordPolicyService->validatePassword($password, $passwordPolicy);
            if (!empty($passwordErrors)) {
                $errors['password'] = $passwordErrors;
            }
        }

        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }

        return response()->json(['message' => 'Validation passed.'], 200);
    }

    // Existing methods...
}

// Define the route for handling password reset errors
Route::get('/api/users/password_reset/error_handling', [ResetPasswordController::class, 'handlePasswordResetErrors']);
