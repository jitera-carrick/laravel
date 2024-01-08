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

    // Removed duplicate validateResetToken method from NEW CODE as it is already defined in CURRENT CODE

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required',
            'password' => [
                'required',
                'min:8', // at least 8 characters
                'regex:/[a-zA-Z]/', // must include at least one letter
                'regex:/\d/', // must include at least one number
                'regex:/[@$!%*#?&]/', // must include a special character
            ],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(['message' => $validator->errors()->first()], 400);
        }

        $validatedData = $validator->validated();

        $passwordReset = PasswordResetToken::where('token', $validatedData['token'])
            ->where('email', $validatedData['email'])
            ->first();

        if (!$passwordReset) {
            return ApiResponse::error(['message' => 'Invalid or expired token.'], 404);
        }

        if (!$passwordReset->created_at->gt(Carbon::now()->subMinutes(config('auth.passwords.users.expire')))) {
            return ApiResponse::error(['message' => 'Invalid or expired token.'], 400);
        }

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user) {
            return ApiResponse::error(['message' => 'User does not exist.'], 404);
        }

        $user->password = Hash::make($validatedData['password']);
        $user->save();

        $passwordReset->used = true;
        $passwordReset->save();

        return ApiResponse::success(['message' => 'Password reset successfully.'], 200);
    }

    public function validateResetToken(ValidateResetTokenRequest $request): JsonResponse
    {
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
