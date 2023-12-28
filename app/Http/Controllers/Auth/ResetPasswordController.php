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
        // Existing code remains unchanged
        // ...
    }

    public function resetPassword(Request $request): JsonResponse
    {
        // Existing code remains unchanged
        // ...
    }

    // Updated method for resetting password with token
    public function resetPasswordWithToken(Request $request, $token): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
        ], [
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.regex' => 'Password does not meet the required policy.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $passwordResetToken = PasswordResetToken::where('token', $token)
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$passwordResetToken) {
                DB::rollBack();
                return ApiResponse::error(['message' => 'Token is required.'], 404);
            }

            $user = $passwordResetToken->user; // Assuming the PasswordResetToken model has a 'user' relationship
            if (!$user) {
                DB::rollBack();
                return ApiResponse::error(['message' => 'User not found.'], 404);
            }

            $user->password = Hash::make($request->password);
            $user->last_password_reset = Carbon::now();
            $user->save();

            $passwordResetToken->used = true;
            $passwordResetToken->save();

            DB::commit();
            return ApiResponse::success(['message' => 'Your password has been successfully reset.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    // ... other methods ...
}
