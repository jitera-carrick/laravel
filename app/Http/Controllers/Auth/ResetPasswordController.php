<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ValidateResetTokenRequest;
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
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;
use Illuminate\Support\Facades\Config;

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

    public function validateResetToken(ValidateResetTokenRequest $request): JsonResponse
    {
        $token = $request->token;
        $passwordResetToken = PasswordResetToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$passwordResetToken) {
            return new ErrorResource(['message' => 'Invalid reset token.'], 400);
        }

        if ($passwordResetToken->expires_at <= Carbon::now()) {
            return new ErrorResource(['message' => 'The reset token is expired.'], 400);
        }

        return new SuccessResource(['message' => 'Reset token is valid.'], 200);
    }

    // ... other methods ...
}
