
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ValidateResetTokenRequest; // Added line
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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|exists:password_reset_tokens,token',
            'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/|confirmed',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email address.',
            'email.exists' => 'No user found with this email address.',
            'token.required' => 'Token is required.',
            'token.exists' => 'Invalid or expired password reset token.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $passwordResetToken = PasswordResetToken::where('token', $request->token)
                ->where('email', $request->email)
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

    /**
     * Validate the password reset token using the ValidateResetTokenRequest.
     *
     * @param  \App\Http\Requests\ValidateResetTokenRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateResetToken(ValidateResetTokenRequest $request): JsonResponse
    {
        // Method implementation remains unchanged
        try {
            $token = $request->token;
            $passwordResetToken = PasswordResetToken::where('token', $token)
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$passwordResetToken) {
                return ApiResponse::error(['message' => 'Invalid reset token.'], 404);
            }

            if ($passwordResetToken->expires_at->isPast()) {
                return ApiResponse::error(['message' => 'The reset token is expired.'], 400);
            }

            return ApiResponse::success(['message' => 'Reset token is valid.']);
        } catch (\Exception $e) {
            return ApiResponse::error(['message' => 'An error occurred while validating the token.'], 500);
        }
    }

    // ... other methods ...
}
