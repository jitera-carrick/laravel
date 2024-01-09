
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;
use App\Services\AuthService;
use App\Helpers\TokenHelper;

class LoginController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    // New login method using LoginRequest and AuthService
    public function login(LoginRequest $request): JsonResponse
    {
        $authService = new AuthService();
        try {
            $sessionToken = $authService->login($request->validated()['email'], $request->validated()['password'], $request->validated()['keep_session']);

            return response()->json([
                'session_token' => $sessionToken,
                'session_expiration' => TokenHelper::calculateSessionExpiration($request->validated()['keep_session'])->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function handleLoginFailure(): JsonResponse
    {
        return response()->json([
            'status' => 200,
            'message' => 'Login failed. Please check your email and password and try again.'
        ], 200);
    }

    // ... other methods ...
}

// Register the route for handling login failure
Route::get('/api/login/failure', [LoginController::class, 'handleLoginFailure']);
