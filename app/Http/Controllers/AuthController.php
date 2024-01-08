
<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();
        $user = $this->authService->findUserByEmail($credentials['email']);

        if ($user && Hash::check($credentials['password'], $user->password_hash)) {
            // Additional logic to handle session token generation and response
        }

        // Return error response if credentials are invalid
    }
}
