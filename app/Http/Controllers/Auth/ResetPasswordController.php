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
use App\Http\Resources\ErrorResource;
use App\Http\Resources\SuccessResource;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Mail;
use App\Exceptions\InvalidTokenException;
use App\Services\AuthService;

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
            'token' => 'required',
            'password' => 'required|min:6|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/|not_in:'.$request->email.'|confirmed',
            'password_confirmation' => 'required',
            'password_reset_token_id' => 'required|exists:password_reset_tokens,id',
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
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }

        DB::beginTransaction();
        try {
            // Check for the presence of 'password_reset_token_id' to determine which logic to follow
            if ($request->has('password_reset_token_id')) {
                // New code logic
                $passwordResetToken = PasswordResetToken::where('id', $request->password_reset_token_id)
                    ->where('used', false)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();
            } else {
                // Existing code logic
                $passwordResetToken = PasswordResetToken::where('email', $request->email)
                    ->where('token', $request->token)
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

            // Determine which password field to use
            $password = $request->has('password') ? $request->password : $request->input('new_password');

            // Use the AuthService to handle password encryption and updating if available
            if (class_exists(AuthService::class)) {
                $authService = new AuthService();
                $passwordData = $authService->encryptPassword($password);
                $user->password = $passwordData['password_hash']; // Update the user's password hash
                $user->password_salt = $passwordData['password_salt']; // Update the user's password salt
            } else {
                $user->password = Hash::make($password);
            }

            $user->last_password_reset = Carbon::now();
            $user->save();

            $passwordResetToken->used = true;
            $passwordResetToken->save();

            // Send confirmation email if the new code logic is used
            if ($request->has('password_reset_token_id')) {
                Mail::to($user->email)->send(new \App\Mail\PasswordResetSuccess($user)); // Assuming PasswordResetSuccess is a valid Mailable
            }

            DB::commit();

            return ApiResponse::success(['message' => 'Your password has been successfully reset.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    public function validateResetToken(ValidateResetTokenRequest $request): JsonResponse
    {
        $token = $request->input('token');

        $passwordResetToken = PasswordResetToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($passwordResetToken) {
            return new SuccessResource(['message' => 'The password reset token is valid.']);
        } else {
            throw new InvalidTokenException('The password reset token is invalid or has expired.');
        }
    }

    // ... other methods ...
}

// End of ResetPasswordController
