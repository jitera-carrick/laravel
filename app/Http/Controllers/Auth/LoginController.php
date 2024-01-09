
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\SessionService;
use Illuminate\Http\Response;

class LoginController extends Controller
{
    protected $authService;
    protected $sessionService;

    public function __construct(AuthService $authService, SessionService $sessionService)
    {
        $this->authService = $authService;
        $this->sessionService = $sessionService;
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $user = User::where('email', $validated['email'])->first();

        if (!$user || !$this->authService->verifyPassword($user, $validated['password'])) {
            return response()->json(['message' => 'Invalid credentials'], Response::HTTP_UNAUTHORIZED);
        }

        $sessionData = $this->sessionService->createSessionToken($user, $validated['keep_session'] ?? false);
        $user->updateSessionInfo($sessionData['session_token'], $sessionData['session_expiration']);

        return response()->json(['session_token' => $user->session_token]);
    }
}
