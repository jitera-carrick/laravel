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
            'password' => 'required|confirmed|min:8',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(['message' => $validator->errors()->first()], 422);
        }

        $input = $request->only(['email', 'token', 'password', 'password_confirmation']);

        if ($input['password'] !== $input['password_confirmation']) {
            return ApiResponse::error(['message' => 'Password confirmation does not match.'], 422);
        }

        DB::beginTransaction();
        try {
            $passwordResetToken = PasswordResetToken::where('email', $input['email'])
                ->where('token', $input['token'])
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$passwordResetToken) {
                DB::rollBack();
                return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 404);
            }

            $user = User::where('email', $input['email'])->first();
            if (!$user) {
                DB::rollBack();
                return ApiResponse::error(['message' => 'User not found.'], 404);
            }

            $user->password = Hash::make($input['password']);
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

    // ... other methods ...
}
