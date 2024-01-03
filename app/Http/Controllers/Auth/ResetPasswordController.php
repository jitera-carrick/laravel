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
use Throwable;

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

    public function validateResetToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }

        try {
            $token = $request->input('token');
            $passwordResetToken = PasswordResetToken::where('token', $token)->first();

            if (!$passwordResetToken) {
                return ApiResponse::error(['message' => 'Invalid reset token.'], 404);
            }

            if ($passwordResetToken->used) {
                return ApiResponse::error(['message' => 'This password reset token has already been used.'], 400);
            }

            if (Carbon::parse($passwordResetToken->expires_at)->isPast()) {
                return ApiResponse::error(['message' => 'The reset token is expired.'], 400);
            }

            return ApiResponse::success(['message' => 'Reset token is valid.'], 200);
        } catch (Throwable $e) {
            return ApiResponse::error(['message' => 'An error occurred while validating the password reset token.'], 500);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        // Existing code remains unchanged
        // ...
    }

    // ... other methods ...
}
