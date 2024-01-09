
<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use App\Helpers\TokenHelper;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $email = $request->input('email');
            $password = $request->input('password');
            $keepSession = $request->input('keep_session');

            $sessionToken = $this->authService->login($email, $password, $keepSession);

            if (!$sessionToken) {
                return response()->json(['message' => 'Invalid credentials.'], 401);
            }

            return response()->json([
                'session_token' => $sessionToken,
                'session_expiration' => TokenHelper::calculateSessionExpiration($keepSession)->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during login.'], 500);
        }
    }
}
