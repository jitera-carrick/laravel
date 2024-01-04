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

class ResetPasswordController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        // The reset method from the new code is used as it contains more comprehensive validation and logic
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|min:' . config('auth.password_length_min') . '|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
        ], [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'token.required' => 'Token is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least ' . config('auth.password_length_min') . ' characters.',
            'password.regex' => 'Password must contain at least one number and one letter.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }

        $passwordResetToken = PasswordResetToken::where('token', $request->token)
            ->where('email', $request->email)
            ->where('used', false)
            ->first();

        if (!$passwordResetToken || $passwordResetToken->expires_at < Carbon::now()) {
            return ApiResponse::error(['message' => 'This password reset token is invalid or has expired.'], 422);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return ApiResponse::error(['message' => 'User not found.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->last_password_reset = Carbon::now();
        $user->save();

        $passwordResetToken->used = true;
        $passwordResetToken->save();

        return ApiResponse::success(['message' => 'Password reset successfully.']);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        // Merge validation rules and messages from both versions
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string', // From existing code, updated to include 'string' type
            'password' => 'required|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/|not_in:'.$request->email.'|confirmed', // From new code, updated min length to 8
            'password_confirmation' => 'required_with:password', // From new code, updated to use 'required_with'
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email address.',
            'token.required' => 'Invalid or expired password reset token.', // From existing code
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.', // Updated min length to 8
            'password.regex' => 'Password must contain both letters and numbers.',
            'password.not_in' => 'Password should not contain the email address.',
            'password.confirmed' => 'Passwords do not match.',
            'password_confirmation.required_with' => 'Password confirmation is required when password is present.', // Updated message for 'required_with'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $passwordResetToken = PasswordResetToken::where('email', $request->email)
                ->where('token', $request->token)
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$passwordResetToken) {
                return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 404);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return ApiResponse::error(['message' => 'User not found.'], 404);
            }

            $user->password = Hash::make($request->password);
            $user->last_password_reset = Carbon::now();
            $user->save();

            $passwordResetToken->used = true;
            $passwordResetToken->save();

            DB::commit();

            return ApiResponse::success(['message' => 'Your password has been successfully reset.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    public function validateResetToken(Request $request): JsonResponse
    {
        // The existing validateResetToken method remains unchanged
        $token = $request->input('token');

        $passwordResetToken = PasswordResetToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        return $passwordResetToken ? ApiResponse::success(['message' => 'The token is valid.']) : ApiResponse::error(['message' => 'The token is invalid or expired.'], 422);
    }

    // ... other methods ...
}
