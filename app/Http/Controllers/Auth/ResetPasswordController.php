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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/|not_in:'.$request->email,
            'password_confirmation' => 'required|string|min:6',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email address.',
            'token.required' => 'Invalid or expired password reset token.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.confirmed' => 'Passwords do not match.',
            'password.regex' => 'Password must contain both letters and numbers.',
            'password.not_in' => 'Password should not contain the email address.',
            'password_confirmation.required' => 'Password confirmation is required.',
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
                DB::rollBack();
                return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 404);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                DB::rollBack();
                return ApiResponse::error(['message' => 'User not found.'], 404);
            }

            // Generate a random salt
            $salt = Hash::make(str_random(22));
            $hashedPassword = Hash::make($request->password . $salt);

            $user->password_hash = $hashedPassword;
            $user->password_salt = $salt;
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
