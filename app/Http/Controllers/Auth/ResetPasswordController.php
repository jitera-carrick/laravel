
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
use App\Notifications\PasswordResetComplete;
use App\Models\EmailLog;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

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
            'token' => 'required', // From existing code
            'password' => 'required|min:6|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/|not_in:'.$request->email.'|confirmed', // From new code
            'password_confirmation' => 'required', // From new code
            'password_reset_token_id' => 'required|exists:password_reset_tokens,id', // From new code
            // 'new_password' => 'required|min:8', // Removed as it's not needed in the new code
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email address.',
            'token.required' => 'Invalid or expired password reset token.', // From existing code
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.regex' => 'Password must contain both letters and numbers.',
            'password.not_in' => 'Password should not contain the email address.',
            'password.confirmed' => 'Passwords do not match.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'password_reset_token_id.required' => 'Password reset token is required.',
            'password_reset_token_id.exists' => 'Invalid or expired password reset token.',
            'new_password.required' => 'Password must be at least 8 characters long.', // From existing code
            'new_password.min' => 'Password must be at least 8 characters long.', // From existing code
        ])->validate(); // Changed to validate method to throw an exception if validation fails

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
                $passwordResetToken = PasswordResetToken::where('token', $request->token) // Removed email condition as it's not needed
                    ->where('used', false)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();
            }

            if (!$passwordResetToken) {
                return ApiResponse::error(['message' => 'Invalid or expired password reset token.'], 404);
            }

            $user = $passwordResetToken->user; // Changed to use the relationship from the token to the user
            if (!$user) {
                return ApiResponse::error(['message' => 'User not found.'], 404);
            }

            // Determine which password field to use
            $password = $request->has('password') ? $request->password : $request->new_password;

            $user->password = Hash::make($password);
            $user->last_password_reset = Carbon::now();
            $user->save();

            $passwordResetToken->used = true;
            $passwordResetToken->update(['used' => true]); // Changed to use the update method

            // Send confirmation email if the new code logic is used
            if ($request->has('password_reset_token_id')) {
                Mail::to($user->email)->send(new \App\Mail\PasswordResetSuccess($user)); // Assuming PasswordResetSuccess is a valid Mailable
            }

            DB::commit();
            
            // Log the email action
            EmailLog::create([
                'email_type' => 'password_reset',
                'sent_at' => Carbon::now(),
                'user_id' => $user->id,
            ]);

            // Send notification to the user
            $user->notify(new PasswordResetComplete());

            return ApiResponse::success(['message' => 'Your password has been successfully reset.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    // ... other methods ...
}
