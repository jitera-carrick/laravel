<?php

use App\Http\Requests\PasswordResetRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

namespace App\Http\Controllers\Auth;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(PasswordResetRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $email = $validatedData['email'];

        $passwordResetService = new PasswordResetService();
        $passwordResetService->sendPasswordResetLink($email);

        return response()->json(['message' => 'If your email address exists in our database, you will receive a password reset link shortly.']);
    }

    public function createPasswordResetRequest(PasswordResetRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Email not found or invalid.'], 400);
        }

        try {
            $validatedData = $validator->validated();
            $email = $validatedData['email'];

            $passwordResetService = new PasswordResetService();
            $passwordResetRequest = $passwordResetService->handlePasswordResetRequest($email);

            return response()->json([
                'status' => 200,
                'message' => 'Password reset request sent successfully.',
                'reset_token' => $passwordResetRequest->reset_token,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // ... existing methods and code ...
}
