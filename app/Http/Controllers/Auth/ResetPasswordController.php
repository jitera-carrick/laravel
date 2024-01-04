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
        // ...
    }

    public function resetPassword(Request $request): JsonResponse
    {
        // Merge validation rules and messages from both versions
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string', // From existing code, added string type for consistency
            'password' => 'required|min:8|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/|not_in:'.$request->email.'|confirmed', // From new code, using the stricter min:8 rule
            'password_confirmation' => 'required', // From new code
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email address.',
            'token.required' => 'Token is required.', // From new code
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters long.', // From new code, using the stricter min:8 rule
            'password.regex' => 'Password must contain at least one letter and one number.', // From new code
            'password.not_in' => 'Password should not contain the email address.', // From new code
            'password.confirmed' => 'Passwords do not match.', // From new code
            'password_confirmation.required' => 'Password confirmation is required.', // From new code
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            $passwordResetToken = PasswordResetToken::where('token', $request->token)
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

            // Send confirmation email if the new code logic is used
            Mail::to($user->email)->send(new \App\Mail\PasswordResetSuccess($user)); // Assuming PasswordResetSuccess is a valid Mailable

            DB::commit();

            return ApiResponse::success(['message' => 'Your password has been successfully reset.']);
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
