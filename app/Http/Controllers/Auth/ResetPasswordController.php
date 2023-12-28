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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required',
            'new_password' => 'required|min:8',
        ], [
            'email.required' => 'Invalid email address.',
            'email.email' => 'Invalid email address.',
            'token.required' => 'Invalid or expired password reset token.',
            'new_password.required' => 'Password must be at least 8 characters long.',
            'new_password.min' => 'Password must be at least 8 characters long.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = $errors->all()[0];
            return ApiResponse::error(['message' => $firstError], 422);
        }

        $input = $request->only(['email', 'token', 'new_password']);

        DB::beginTransaction();
        try {
            $passwordResetToken = PasswordResetToken::where('email', $input['email'])
                ->where('token', $input['token'])
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$passwordResetToken) {
                return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 404);
            }

            $user = User::where('email', $input['email'])->first();
            if (!$user) {
                return ApiResponse::error(['message' => 'Invalid email address.'], 404);
            }

            $user->password = Hash::make($input['new_password']);
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

    // New method to validate the reset token
    public function validateResetToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ], [
            'token.required' => 'Token is required.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = $errors->all()[0];
            return ApiResponse::error(['message' => $firstError], 422);
        }

        $token = $request->input('token');

        $passwordResetToken = PasswordResetToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$passwordResetToken) {
            return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 422);
        }

        $passwordResetToken->update(['used' => true]);

        return ApiResponse::success(['message' => 'Token is valid. Proceed to password reset.']);
    }

    // ... other methods ...
}
