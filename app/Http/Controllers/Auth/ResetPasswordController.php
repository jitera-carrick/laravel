
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
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ValidateResetTokenRequest;
use App\Http\Requests\ValidatePasswordResetTokenRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;

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

    public function validateResetToken(ValidatePasswordResetTokenRequest $request): JsonResponse
    {
        $token = $request->token;
        $passwordReset = PasswordReset::where('token', $token)->first();

        if (!$passwordReset) {
            return new ErrorResource(['message' => 'Token is invalid or expired.']);
        }

        $isTokenExpired = $passwordReset->isTokenExpired();

        if ($isTokenExpired) {
            return new ErrorResource(['message' => 'Token is expired']);
        }

        return new SuccessResource(['message' => 'Token is valid.']);
    }

    // ... other methods ...

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        if ($validatedData['password'] !== $validatedData['password_confirmation']) {
            return new ErrorResource(['message' => 'Passwords do not match.']);
        }

        $passwordReset = PasswordReset::where('token', $validatedData['token'])
            ->where('email', $validatedData['email'])
            ->first();

        if (!$passwordReset || $passwordReset->isTokenExpired()) {
            return new ErrorResource(['message' => 'Token is invalid or expired.']);
        }

        $user = User::where('email', $validatedData['email'])->first();
        if (!$user) {
            return new ErrorResource(['message' => 'User not found.']);
        }

        $user->password = Hash::make($validatedData['password']);
        $user->save();

        $passwordReset->delete();

        return new SuccessResource(['message' => 'Password has been reset successfully.']);
    }

    // ... other methods ...
}
