<?php

namespace App\Http\Controllers;

use App\Http\Requests\SessionRequest;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;

class SessionController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function maintainSession(SessionRequest $request): JsonResponse
    {
        $sessionToken = $request->input('session_token');
        $maintainResult = $this->sessionService->maintain($sessionToken);

        if ($maintainResult === 'not_found') {
            return response()->json(['error' => 'Invalid session token.'], 401);
        } elseif ($maintainResult === 'expired') {
            return response()->json(['error' => 'Session token has expired.'], 401);
        } elseif ($maintainResult === true) {
            return response()->json(['message' => 'Session maintained successfully.'], 200);
        } else {
            return response()->json(['error' => 'An unexpected error occurred on the server.'], 500);
        }
    }

    // ... other methods ...
}
