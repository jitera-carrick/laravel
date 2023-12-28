<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
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
        $input = $request->only(['email', 'token', 'password', 'password_confirmation']);

        if ($input['password'] !== $input['password_confirmation']) {
            return ApiResponse::error(['message' => 'Password confirmation does not match.'], 422);
        }

        DB::beginTransaction();
        try {
            $passwordResetToken = PasswordResetToken::where('email', $input['email'])
                ->where('token', $input['token'])
                ->where('used', false)
                ->where('created_at', '>', Carbon::now()->subHours(2))
                ->first();

            if (!$passwordResetToken) {
                return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 422);
            }

            $newPasswordHash = Hash::make($input['password']);

            $user = User::where('email', $input['email'])->first();
            $user->password = $newPasswordHash;
            $user->last_password_reset = Carbon::now();
            $user->save();

            $passwordResetToken->used = true;
            $passwordResetToken->save();

            DB::commit();

            // MailService::sendPasswordResetConfirmation($user->email);

            return ApiResponse::success(['message' => 'Password has been successfully reset.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    /**
     * Validate the password reset token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateResetToken(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $token = $request->input('token');

        if (!$email) {
            return ApiResponse::error(['message' => 'Invalid email address.'], 400);
        }

        if (!$token) {
            return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 422);
        }

        $passwordResetToken = PasswordResetToken::where('email', $email)
            ->where('token', $token)
            ->where('used', false)
            ->where('created_at', '>', Carbon::now()->subHours(2))
            ->first();

        if (!$passwordResetToken) {
            return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 404);
        }

        return ApiResponse::success(['message' => 'The password reset token is valid.']);
    }

    // ... other methods ...
}
