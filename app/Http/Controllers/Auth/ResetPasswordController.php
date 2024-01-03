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
            'token' => 'required|string', // Merged from new code
            'password' => 'required|string|min:6|confirmed|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/|not_in:'.$request->email, // Merged from new code
            'password_confirmation' => 'required_with:password|same:password', // Merged from new code
            'password_reset_token_id' => 'sometimes|required|exists:password_reset_tokens,id', // Merged from new code
            'new_password' => 'sometimes|required|min:8', // From existing code
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email address.',
            'token.required' => 'The reset token is required.', // Merged from new code
            'token.string' => 'The reset token must be a string.', // Merged from new code
            'password.required' => 'The password is required.', // Merged from new code
            'password.string' => 'The password must be a string.', // Merged from new code
            'password.min' => 'The password must be at least 6 characters.', // Merged from new code
            'password.confirmed' => 'The password confirmation does not match.', // Merged from new code
            'password.regex' => 'The password must contain at least one letter and one number.', // Merged from new code
            'password.not_in' => 'Password should not contain the email address.', // Merged from new code
            'password_confirmation.required_with' => 'The password confirmation is required when password is present.', // Merged from new code
            'password_confirmation.same' => 'The password confirmation must match the password.', // Merged from new code
            'password_reset_token_id.required' => 'Password reset token is required.', // Merged from new code
            'password_reset_token_id.exists' => 'Invalid or expired password reset token.', // Merged from new code
            'new_password.required' => 'New password is required.', // From existing code
            'new_password.min' => 'New password must be at least 8 characters long.', // From existing code
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
                $passwordResetToken = PasswordResetToken::where('token', $request->token)
                    ->where('used', false)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();
            }

            if (!$passwordResetToken) {
                DB::rollBack();
                return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 404);
            }

            $user = User::where('email', $passwordResetToken->email)->first();
            if (!$user) {
                DB::rollBack();
                return ApiResponse::error(['message' => 'User not found.'], 404);
            }

            // Determine which password field to use
            $password = $request->has('password') ? $request->password : $request->new_password;

            // Encrypt the new password to create a password_hash and password_salt
            $passwordHash = Hash::make($password);
            // Assuming the User model has password_salt attribute
            $passwordSalt = ''; // Generate password salt if necessary

            $user->password = $passwordHash;
            $user->password_salt = $passwordSalt; // Update password_salt if necessary
            $user->last_password_reset = Carbon::now();
            $user->save();

            $passwordResetToken->used = true;
            $passwordResetToken->save();

            DB::commit();

            // Send confirmation email if the new code logic is used
            if ($request->has('password_reset_token_id')) {
                Mail::to($user->email)->send(new \App\Mail\PasswordResetSuccess($user)); // Assuming PasswordResetSuccess is a valid Mailable
            }

            return ApiResponse::success(['message' => 'Your password has been successfully reset.']);
        } catch (Throwable $e) {
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    // ... other methods ...
}
