<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Models\User;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PasswordResetRequestController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Email not found or invalid.'], 422);
        }

        $validatedData = $validator->validated();
        $user = User::where('email', $validatedData['email'])->first();

        $passwordResetRequest = $this->passwordResetService->createResetToken($user->email);

        return response()->json([
            'status' => 200,
            'password_reset_request' => $passwordResetRequest
        ], 200);
    }
}
