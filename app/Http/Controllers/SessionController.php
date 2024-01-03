<?php

namespace App\Http\Controllers;

use App\Http\Requests\SessionRequest;
use App\Services\SessionService;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;

class SessionController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    // ... other methods ...

    public function extendUserSession(SessionRequest $request): JsonResponse
    {
        try {
            $sessionToken = $request->input('session_token');
            $keepSession = $request->input('keep_session', false);
            $userSession = $this->sessionService->findByToken($sessionToken);

            if (!$userSession) {
                return ApiResponse::error(['message' => 'Invalid session token.'], 404);
            }

            if ($this->sessionService->isSessionExpired($userSession)) {
                return ApiResponse::error(['message' => 'Session has already expired.'], 401);
            }

            $extendedSession = $this->sessionService->extendSession($userSession, $keepSession);

            return ApiResponse::success([
                'message' => 'Session extended successfully.',
                'session_expiration' => $extendedSession->session_expiration
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error(['message' => 'An error occurred while extending the session.'], 500);
        }
    }
}
