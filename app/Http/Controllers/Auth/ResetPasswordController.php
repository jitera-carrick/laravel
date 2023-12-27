<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Mail\ResetPasswordConfirmationMail;
use App\Services\PasswordPolicyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Log;

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
        // Use the token from the request if not provided as a parameter
        $token = $token ?? $request->route('token');

        // Check if the token is provided
        if (empty($token)) {
            return response()->json(['valid' => false, 'message' => 'Token is required.'], 400);
        }

        // Find the password reset token in the database
        $passwordResetToken = PasswordResetToken::where('token', $token)->first();

        // Check if the token exists and is not expired
        if (!$passwordResetToken || $passwordResetToken->expires_at < now()) {
            return response()->json(['valid' => false, 'message' => 'Invalid or expired token.'], 404);
        }

        // Return a success response if the token is valid
        return response()->json(['valid' => true, 'message' => 'Token is valid. You can now set a new password.'], 200);
    }

    // Updated method to handle password reset errors
    public function handlePasswordResetErrors(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'error_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Error code is required.'], 422);
        }

        $errorCode = $request->input('error_code') ?? $request->query('error_code');

        // Merge the error handling from new and existing code
        $errorMessages = $this->getErrorMessages();

        if (array_key_exists($errorCode, $errorMessages)) {
            return response()->json(['status' => 200, 'message' => $errorMessages[$errorCode]]);
        }

        // Handle the error code using switch case as fallback
        switch ($errorCode) {
            // Add cases for recognized error codes here
            default:
                return response()->json(['message' => 'Unknown error.'], 400);
        }
    }

    private function getErrorMessages()
    {
        return [
            // Define recognized error codes and their messages here
            'ERROR_CODE_1' => 'Error message for code 1',
            'ERROR_CODE_2' => 'Error message for code 2',
            // ...
        ];
    }

    // Existing methods...
}

// Update the route for handling password reset errors to use PUT method
// and keep the GET route for backward compatibility
Route::put('/api/users/password_reset/error_handling', [ResetPasswordController::class, 'handlePasswordResetErrors']);
Route::get('/api/users/password_reset/error_handling', [ResetPasswordController::class, 'handlePasswordResetErrors']);

// Define the route for validating password reset token
Route::get('/api/users/password_reset/validate/{token}', [ResetPasswordController::class, 'validatePasswordResetToken']);
