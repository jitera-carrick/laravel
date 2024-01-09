<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Http\Middleware\Authenticate;
use App\Models\LoginAttempt;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->middleware(Authenticate::class);
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $sessionToken = $this->authService->attemptLogin($validated);

            return response()->json(['session_token' => $sessionToken]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function cancelLogin(): JsonResponse
    {
        $user = Auth::user();
        LoginAttempt::where('user_id', $user->id)->delete();

        return response()->json([
            'message' => 'Your login process has been canceled.'
        ]);
    }

    // ... other methods ...
}
