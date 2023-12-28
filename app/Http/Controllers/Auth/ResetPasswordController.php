<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Mail;
use Exception;

class ResetPasswordController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        // Existing code remains unchanged
        // ...
    }

    public function resetPassword(Request $request): JsonResponse
    {
        // Existing code remains unchanged
        // ...
    }

    // New method for validating password reset token
    public function validatePasswordResetToken($token): JsonResponse
    {
        if (empty($token)) {
            return ApiResponse::error(['message' => 'Token is required.'], 400);
        }

        try {
            $passwordResetToken = PasswordResetToken::where('token', $token)
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$passwordResetToken) {
                return ApiResponse::error(['message' => 'Invalid or expired token.'], 404);
            }

            return ApiResponse::success(['message' => 'Token is valid. You may proceed to reset your password.']);
        } catch (Exception $e) {
            return ApiResponse::error(['message' => 'An error occurred while validating the token.'], 500);
        }
    }

    // ... other methods ...
}
