
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ValidateResetTokenRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;

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

    public function validateResetToken(ResetPasswordRequest $request): JsonResponse
    {
        $token = $request->token;
        $passwordResetToken = PasswordResetToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$passwordResetToken) {
            return new ErrorResource(['message' => 'Token is invalid or expired.']);
        }

        return new SuccessResource(['message' => 'Token is valid.']);
    }

    // ... other methods ...

    // Existing methods remain unchanged
    // ...

    public function resetPassword(Request $request): JsonResponse
    {
        // Existing code remains unchanged
        $resetPasswordRequest = ResetPasswordRequest::createFrom($request);
        $validatedData = $resetPasswordRequest->validated();

        $passwordReset = PasswordResetToken::where('token', $validatedData['token'])
            ->where('email', $validatedData['email'])
            ->first();

        if (!$passwordReset || !$passwordReset->created_at->gt(Carbon::now()->subMinutes(config('auth.passwords.users.expire')))) {
            return ApiResponse::error(['message' => 'This password reset token is invalid or has expired.'], 422);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user) {
            return ApiResponse::error(['message' => 'User does not exist.'], 404);
        }

        $user->password = app('hash')->make($validatedData['password']);
        $user->save();

        $passwordReset->delete();

        return ApiResponse::success(['message' => 'Your password has been successfully reset.']);
    }

    public function validateResetToken(ValidateResetTokenRequest $request): JsonResponse
    {
        // Existing code remains unchanged
        $token = $request->input('token');
        $passwordResetToken = PasswordResetToken::where('token', $token)
            ->where('used', false)
            ->first();

        if (!$passwordResetToken) {
            return new ErrorResource(['message' => 'Invalid or expired password reset token.']);
        }

        $tokenLifetime = config('auth.passwords.users.expire') * 60;
        $tokenCreatedAt = Carbon::parse($passwordResetToken->created_at);
        $tokenExpired = $tokenCreatedAt->addSeconds($tokenLifetime)->isPast();

        if ($tokenExpired) {
            return new ErrorResource(['message' => 'Token is expired']);
        }

        return new SuccessResource(['message' => 'Token is valid']);
    }

    // ... other methods ...
}
