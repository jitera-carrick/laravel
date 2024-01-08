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
use App\Models\PasswordResetRequest;
use Illuminate\Support\Facades\Mail;
use App\Mail\PasswordResetSuccess;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;
use App\Http\Requests\ValidateResetTokenRequest;

class ResetPasswordController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
        $this->middleware('guest')->only('validateResetToken');
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
            'password' => 'required|string|min:6|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
        ], [
            'email.required' => 'Please enter a valid email address.',
            'token.required' => 'Invalid or expired reset token.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.regex' => 'Password must contain both letters and numbers.',
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
                ->firstOrFail();

            $user = $passwordResetToken->user()->firstOrFail();
            $password = $request->password;

            $user->password = Hash::make($password);
            $user->last_password_reset = Carbon::now();
            $user->save();

            $passwordResetToken->used = true;
            $passwordResetToken->save();

            Mail::to($user->email)->send(new PasswordResetSuccess($user));

            DB::commit();

            return ApiResponse::success(['message' => 'Password reset successful.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    public function validateResetToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }

        $token = $request->input('token');
        $passwordResetToken = PasswordResetToken::where('token', $token)
            ->where('used', false)
            ->first();

        if (!$passwordResetToken) {
            return ApiResponse::error(['message' => 'Invalid reset token.'], 404);
        }

        $tokenLifetime = config('auth.passwords.users.expire') * 60;
        $tokenCreatedAt = Carbon::parse($passwordResetToken->created_at);
        $tokenExpired = $tokenCreatedAt->addSeconds($tokenLifetime)->isPast();

        if ($tokenExpired) {
            return ApiResponse::error(['message' => 'The reset token has expired.'], 400);
        }

        return ApiResponse::success(['message' => 'Reset token is valid.'], 200);
    }

    // ... other methods ...
}
