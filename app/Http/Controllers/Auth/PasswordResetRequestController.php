
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest as PasswordResetRequestValidation;
use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;

class PasswordResetRequestController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function store(PasswordResetRequestValidation $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = User::where('email', $validatedData['email'])->first();

        $passwordResetRequest = $this->passwordResetService->createResetToken($user->email);

        // Create a new password reset request entry
        $newPasswordResetRequest = new PasswordResetRequest([
            'user_id' => $user->id,
            'reset_token' => $passwordResetRequest->token,
            'token_expiration' => $passwordResetRequest->expires_at,
        ]);
        $newPasswordResetRequest->save();

        // TODO: Send password reset email to the user

        return response()->json([
            'status' => 200,
            'message' => 'Password reset request created successfully.',
            'password_reset_request' => $newPasswordResetRequest
        ], 200);
    }
}
