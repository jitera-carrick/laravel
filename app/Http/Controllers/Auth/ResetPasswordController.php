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
        // Merged validation rules and messages from both versions
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
                'not_in:'.$request->email,
            ],
            'password_confirmation' => 'required_with:password|same:password',
        ], [
            'email.required' => 'Invalid or non-existent email address.',
            'email.email' => 'Invalid or non-existent email address.',
            'email.exists' => 'Invalid or non-existent email address.',
            'token.required' => 'Invalid or expired reset token.',
            'token.string' => 'Invalid or expired reset token.',
            'password.required' => 'Password does not meet the complexity requirements.',
            'password.string' => 'Password does not meet the complexity requirements.',
            'password.min' => 'Password does not meet the complexity requirements.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.regex' => 'Password does not meet the complexity requirements.',
            'password.not_in' => 'Password does not meet the complexity requirements.',
            'password_confirmation.required_with' => 'Password confirmation does not match.',
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
                return ApiResponse::error(['message' => 'Invalid or expired reset token.'], 404);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                DB::rollBack();
                return ApiResponse::error(['message' => 'Invalid or non-existent email address.'], 404);
            }

            $user->password = Hash::make($request->password);
            $user->last_password_reset = Carbon::now();
            $user->save();

            $passwordResetToken->used = true;
            $passwordResetToken->save();

            DB::commit();

            Mail::to($user->email)->send(new \App\Mail\PasswordResetSuccess($user)); // Assuming PasswordResetSuccess is a valid Mailable

            return ApiResponse::success(['message' => 'Password reset successfully.']);
        } catch (Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    // ... other methods ...
}
