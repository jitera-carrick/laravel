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

        // Check if password confirmation matches
        if ($input['password'] !== $input['password_confirmation']) {
            return ApiResponse::error(['message' => 'Password confirmation does not match.'], 422);
        }

        // Begin transaction
        DB::beginTransaction();
        try {
            // Find the token in the password_reset_tokens table
            $passwordResetToken = PasswordResetToken::where('email', $input['email'])
                ->where('token', $input['token'])
                ->where('used', false)
                ->where('created_at', '>', Carbon::now()->subHours(2)) // Assuming tokens are valid for 2 hours
                ->first();

            if (!$passwordResetToken) {
                return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 422);
            }

            // Hash the new password
            $newPasswordHash = Hash::make($input['password']);

            // Update the user's password
            $user = User::where('email', $input['email'])->first();
            $user->password = $newPasswordHash;
            $user->last_password_reset = Carbon::now();
            $user->save();

            // Mark the token as used
            $passwordResetToken->used = true;
            $passwordResetToken->save();

            // Commit transaction
            DB::commit();

            // Send confirmation email (pseudo code, assuming MailService exists)
            // MailService::sendPasswordResetConfirmation($user->email);

            return ApiResponse::success(['message' => 'Password has been successfully reset.']);
        } catch (\Exception $e) {
            // Rollback transaction
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    // ... other methods ...
}
