<?php

namespace App\Http\Controllers;

use App\Http\Requests\SessionRequest;
use App\Services\SessionService;
use App\Utils\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    // New method to maintain user session
    public function maintainUserSession(Request $request, $userId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'keep_session' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(['message' => $validator->errors()->first()], 400);
        }

        try {
            $keepSession = filter_var($request->input('keep_session'), FILTER_VALIDATE_BOOLEAN);
            $userSession = $this->sessionService->findByUserId($userId);

            if (!$userSession) {
                return ApiResponse::error(['message' => 'User ID does not exist.'], 404);
            }

            if (!$request->user() || $request->user()->id != $userId) {
                return ApiResponse::error(['message' => 'Unauthorized'], 401);
            }

            $updatedSession = $this->sessionService->maintainSession($userSession, $keepSession);

            return ApiResponse::success([
                'message' => 'Session preference updated successfully',
                'session_expiration' => $updatedSession->session_expiration
            ]);
        } catch (\Exception $e) {
            return ApiResponse::error(['message' => 'An error occurred while updating the session preference.'], 500);
        }
    }
}
