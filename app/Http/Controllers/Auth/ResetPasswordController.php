<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Carbon;

class ResetPasswordController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        $email = $request->input('email');
        $token = $request->input('token');
        $password = $request->input('password');
        $passwordConfirmation = $request->input('password_confirmation');

        // Validate the new password and its confirmation
        if ($password !== $passwordConfirmation) {
            return response()->json(['message' => 'Password confirmation does not match.'], 422);
        }

        // Check the password policy requirements
        // Assuming there's a method in the PasswordResetService to validate the password policy
        if (!$this->passwordResetService->validatePasswordPolicy($password)) {
            return response()->json(['message' => 'Password does not meet the policy requirements.'], 422);
        }

        // Begin transaction
        DB::beginTransaction();
        try {
            // Check the "password_reset_tokens" table
            $passwordResetToken = PasswordResetToken::where('email', $email)
                ->where('token', $token)
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$passwordResetToken) {
                DB::rollBack();
                return response()->json(['message' => 'Invalid or expired password reset token.'], 422);
            }

            // Retrieve the corresponding user
            $user = User::find($passwordResetToken->user_id);
            if (!$user) {
                DB::rollBack();
                return response()->json(['message' => 'User not found.'], 404);
            }

            // Encrypt the new password and update the user
            $user->password_hash = Hash::make($password);
            $user->password_reset_required = false;
            $user->save();

            // Mark the token as "used"
            $passwordResetToken->used = true;
            $passwordResetToken->save();

            // Commit transaction
            DB::commit();

            // Send a confirmation to the user
            // Assuming there's a method in the PasswordResetService to send the confirmation
            $this->passwordResetService->sendPasswordResetConfirmation($user->email);

            return response()->json(['message' => 'Password has been reset successfully.'], 200);
        } catch (\Exception $e) {
            // Rollback transaction on any exception
            DB::rollBack();
            return response()->json(['message' => 'Failed to reset password.'], 500);
        }
    }

    // Existing methods...
}
