<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SessionMaintenanceRequest;
use App\Http\Requests\LogoutRequest;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\Session;

class SessionController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function maintainSession(SessionMaintenanceRequest $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');
        $keepSession = $request->input('keep_session');

        try {
            $userSession = $this->sessionService->findByToken($sessionToken);

            if (!$userSession) {
                return response()->json(['message' => 'Invalid session token.'], 404);
            }

            $newExpiration = $keepSession ? now()->addDays(90) : now()->addHours(24);
            $userSession->session_expiration = $newExpiration;
            $userSession->save();

            return response()->json([
                'session_maintenance_status' => 'success',
                'new_expiration' => $newExpiration->toDateTimeString(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while maintaining the session.'], 500);
        }
    }

    // ... other methods ...

    public function logout(LogoutRequest $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');
        try {
            if (!$sessionToken) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $deactivated = $this->sessionService->deactivateSession($sessionToken);
            if ($deactivated) {
                return response()->json(null, 204);
            } else {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    // ... other methods ...
}
