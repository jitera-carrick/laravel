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
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email address.',
            'email.exists' => 'Email address not found.',
            'token.required' => 'Token is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Passwords do not match.',
            'password.regex' => 'Password is too weak.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $user = User::where('email', $request->email)->first();
            $passwordResetToken = PasswordResetToken::where('email', $user->email)
                ->where('token', $request->token)
                ->where('expires_at', '>', Carbon::now())
                ->where('used', false)
                ->first();

            if (!$passwordResetToken) {
                DB::rollBack();
                return ApiResponse::error(['message' => 'Invalid or expired reset token.'], 400);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            $passwordResetToken->used = true;
            $passwordResetToken->save();

            DB::commit();
            return ApiResponse::success(['message' => 'Password reset successful.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    public function validateResetToken(ValidateResetTokenRequest $request): JsonResponse
    {
        $token = $request->token;
        $passwordResetToken = PasswordResetToken::where('token', $token)
            ->where('used', false)
            ->first();

        if ($passwordResetToken && $passwordResetToken->created_at->addSeconds(Config::get('auth.passwords.users.expire') * 60) > Carbon::now()) {
            return new SuccessResource(['message' => 'The password reset token is valid.']);
        } else {
            return new ErrorResource(['message' => 'The password reset token is invalid or expired.']);
        }
    }

    // ... other methods ...
}
