
<?php

use App\Http\Requests\PasswordResetRequest;
use App\Services\PasswordResetService;
use Illuminate\Http\JsonResponse;

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

    // ... existing methods and code ...
}
