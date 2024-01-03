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
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ResetPasswordController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService = null)
    {
        $this->middleware('guest');
        $this->passwordResetService = $passwordResetService;
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        return $this->resetPassword($request);
    }

    public function validateResetToken(Request $request, $token): JsonResponse
    {
        if (empty($token)) {
            return ApiResponse::error(['message' => 'Token is required.'], 400);
        }

        try {
            $passwordResetToken = PasswordResetToken::where('token', $token)
                ->where('used', false)
                ->where('expires_at', '>=', Carbon::now())
                ->firstOrFail();

            return ApiResponse::success(['message' => 'Token is valid. You may proceed to reset your password.']);
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(['message' => 'Invalid or expired token.'], 404);
        } catch (\Exception $e) {
            return ApiResponse::error(['message' => 'An error occurred while validating the password reset token.'], 500);
        }
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/|not_in:'.$request->email.'|confirmed',
            'password_confirmation' => 'required',
            'password_reset_token_id' => 'sometimes|required|exists:password_reset_tokens,id',
            'new_password' => 'sometimes|required|min:8',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email address.',
            'token.required' => 'Invalid or expired password reset token.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.regex' => 'Password must contain both letters and numbers.',
            'password.not_in' => 'Password should not contain the email address.',
            'password.confirmed' => 'Passwords do not match.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'password_reset_token_id.required' => 'Password reset token is required.',
            'password_reset_token_id.exists' => 'Invalid or expired password reset token.',
            'new_password.required' => 'New password is required.',
            'new_password.min' => 'New password must be at least 8 characters long.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            if ($request->has('password_reset_token_id')) {
                $passwordResetToken = PasswordResetToken::where('id', $request->password_reset_token_id)
                    ->where('used', false)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();
            } else {
                $passwordResetToken = PasswordResetToken::where('token', $request->token)
                    ->where('used', false)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();
            }

            if (!$passwordResetToken) {
                return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 404);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return ApiResponse::error(['message' => 'User not found.'], 404);
            }

            $password = $request->has('password') ? $request->password : $request->new_password;
            $user->password = Hash::make($password);
            $user->last_password_reset = Carbon::now();
            $user->save();

            $passwordResetToken->used = true;
            $passwordResetToken->save();

            if ($request->has('password_reset_token_id')) {
                Mail::to($user->email)->send(new \App\Mail\PasswordResetSuccess($user));
            }

            DB::commit();

            return ApiResponse::success(['message' => 'Your password has been successfully reset.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    // ... other methods ...
}
