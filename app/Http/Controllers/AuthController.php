
<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->attemptLogin($request->email, $request->password);
        return $token ? response()->json(['token' => $token], 200) : response()->json(['error' => 'Unauthorized'], 401);
    }
}
