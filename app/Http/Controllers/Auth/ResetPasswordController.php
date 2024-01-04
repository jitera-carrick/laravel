
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
        $validator = Validator::make($request->all(), [
            'password_confirmation' => 'required|same:password',
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
        ], [
            'email.required' => 'Please enter a valid email address.',
            'token.required' => 'The reset token is expired or invalid.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.regex' => 'Password must contain both letters and numbers.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }
        $validatedData = $validator->validated();

        DB::beginTransaction();
        try {
            $passwordResetToken = PasswordResetToken::where('token', $request->token)
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();
            
            if (!$passwordResetToken) {
                DB::rollBack();
                return ApiResponse::error(['message' => 'The reset token is expired or invalid.'], 404);
            }

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                DB::rollBack();
                return ApiResponse::error(['message' => 'User not found with the provided email.'], 404);
            }

            // Use the AuthService to handle password encryption and updating if available
            if (class_exists(AuthService::class)) {
                $authService = new AuthService();
                $passwordData = $authService->encryptPassword($request->password);
                $user->password = $passwordData['password_hash']; // Update the user's password hash
                $user->password_salt = $passwordData['password_salt']; // Update the user's password salt if available
            } else {
                $user->password = Hash::make($request->password);
            }

            $user->save();

            $passwordResetToken->used = true;
            $passwordResetToken->save(); // Mark the token as used

            DB::commit();

            return ApiResponse::success(['message' => 'Your password has been reset successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error(['message' => 'An error occurred while resetting the password.'], 500);
        }
    }

    public function validateResetToken(ValidateResetTokenRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ], [
            'token.required' => 'The reset token is required.',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 422);
        }

        $token = $request->input('token');

        $passwordResetToken = PasswordResetToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($passwordResetToken) {
            return ApiResponse::success(['message' => 'The password reset token is valid.'], 200);
        } else {
            return ApiResponse::error(['message' => 'The reset token is invalid or has expired.'], 404);
        }
    }

    // ... other methods ...
}

// End of ResetPasswordController
