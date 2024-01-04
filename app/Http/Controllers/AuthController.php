
<?php

namespace App\Http\Controllers;

use App\Http\Requests\LogoutRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle user logout, invalidate session, and update user status.
     *
     * @param LogoutRequest $request
     * @return JsonResponse
     */
    public function logout(LogoutRequest $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');
        $user = $this->authService->findUserBySessionToken($sessionToken);

        if (!$user) {
            return response()->json(['message' => 'Invalid session token.'], 401);
        }

        $logoutSuccess = $this->authService->invalidateSession($user);

        if ($logoutSuccess) {
            return response()->json(['message' => 'Logout successful.']);
        }

        return response()->json(['message' => 'Logout failed.'], 500);
    }
}
