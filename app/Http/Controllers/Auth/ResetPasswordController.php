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
use Illuminate\Support\Facades\Route; // Import Route facade for defining routes

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
        // Existing setNewPassword method code...
    }

    // Method to validate the password reset token
    public function validatePasswordResetToken(Request $request, $token = null): JsonResponse
    {
        // Existing validatePasswordResetToken method code...
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
