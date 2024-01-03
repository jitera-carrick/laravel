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
use Throwable;

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

    public function validateResetToken(Request $request): JsonResponse
    {
        // New code method
        // ...
    }

    public function resetPassword(Request $request): JsonResponse
    {
        // Merge validation rules and messages from both versions
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string', // From new code, added string validation
            'password' => 'required|min:6|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/|not_in:'.$request->email.'|confirmed', // From new code
            'password_confirmation' => 'required_with:password|same:password', // From new code
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email address.',
            'token.required' => 'Invalid or expired password reset token.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.regex' => 'Password must contain both letters and numbers.',
            'password.not_in' => 'Password should not contain the email address.',
            'password.confirmed' => 'Passwords do not match.',
            'password_confirmation.required_with' => 'Password confirmation is required when password is present.',
            'password_confirmation.same' => 'Password confirmation does not match.',
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
                DB::rollBack();
                return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 404);
            }

            $user = User::where('email', $request->email)->first();
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

            // Send confirmation email
            Mail::to($user->email)->send(new \App\Mail\PasswordResetSuccess($user)); // Assuming PasswordResetSuccess is a valid Mailable

            return ApiResponse::success(['message' => 'Your password has been successfully reset.']);
        } catch (Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    // ... other methods ...
}
