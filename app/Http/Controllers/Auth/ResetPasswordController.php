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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResetPasswordController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function reset(ResetPasswordRequest $request): JsonResponse
    {
        // ... existing reset method code ...
    }

    // Existing methods...

    public function confirmReset(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required',
            'new_password' => 'required|min:8', // Add your password complexity rules here
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Check the password policy requirements
        if (!$this->passwordResetService->validatePasswordPolicy($request->new_password)) {
            return response()->json(['message' => 'Password does not meet the required complexity.'], 422);
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
                return response()->json(['message' => 'Invalid or expired password reset token.'], 422);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                DB::rollBack();
                return response()->json(['message' => 'User not found.'], 404);
            }

            $user->password_hash = Hash::make($request->new_password);
            $user->password_reset_required = false;
            $user->save();

            $passwordResetToken->used = true;
            $passwordResetToken->save();

            DB::commit();
            return response()->json(['message' => 'Your password has been reset successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to reset password.'], 500);
        }
    }
}
