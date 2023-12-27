<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\PasswordReset;
use App\Models\PasswordResetToken;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordConfirmationMail;
use App\Services\PasswordPolicyService;
use Illuminate\Http\JsonResponse;
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

    /**
     * Validate the password reset token.
     *
     * @param Request $request
     * @param string|null $token
     * @return JsonResponse
     */
    public function validatePasswordResetToken(Request $request, $token = null): JsonResponse
    {
        // Use the token from the request if not provided as a parameter
        $token = $token ?? $request->route('token');

        // Check if the token is provided
        if (empty($token)) {
            return response()->json(['message' => 'Token is required.'], 400);
        }

        // Find the password reset token in the database
        $passwordResetToken = PasswordResetToken::where('token', $token)->first();

        // Check if the token exists and is not expired
        if (!$passwordResetToken || $passwordResetToken->isExpired()) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        // Return a success response if the token is valid
        return response()->json(['status' => 200, 'message' => 'Token is valid. You may proceed to set a new password.'], 200);
    }

    // Existing methods...
}
