<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest as PasswordResetRequestValidation;
use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use App\Notifications\PasswordResetNotification;

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

        if (empty($validatedData['email'])) {
            return response()->json([
                'status' => 400,
                'message' => 'Email is required.'
            ], 400);
        }

        $user = User::where('email', $validatedData['email'])->first();
        
        if (!$user) {
            return response()->json([
                'status' => 400,
                'message' => 'Email not found.'
            ], 400);
        }

        $passwordResetRequest = $this->passwordResetService->createResetToken($user->email);

        // Create a new password reset request entry
        $newPasswordResetRequest = new PasswordResetRequest([
            'user_id' => $user->id,
            'reset_token' => $passwordResetRequest->token,
            'token_expiration' => $passwordResetRequest->expires_at,
        ]);
        $newPasswordResetRequest->save();
        
        // Send password reset email to the user
        $user->notify(new PasswordResetNotification($newPasswordResetRequest));
        
        return response()->json([
            'status' => 201,
            'message' => 'Password reset request sent successfully. Please check your email for further instructions.',
            'password_reset_request' => $newPasswordResetRequest
        ], 201);
    }
}
