
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Services\AuthService;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PasswordResetController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function resetPassword(PasswordResetRequest $request): JsonResponse
    {
        $user = User::where('email', $request->validated()['email'])->first();

        if ($user) {
            $this->authService->createPasswordResetRequest($user);
        }

        return response()->json(['message' => 'If your email address is registered in our system, you will receive a password reset email shortly.']);
    }
}
