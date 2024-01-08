<?php

use App\Http\Requests\SessionRequest;
use App\Services\SessionService;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

namespace App\Http\Controllers;

class SessionController extends Controller
{
    protected $sessionService;
    protected $authService;

    public function __construct(SessionService $sessionService, AuthService $authService)
    {
        $this->sessionService = $sessionService;
        $this->authService = $authService;
    }

    public function maintainSession(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid session token.'], 400);
        }

        $sessionToken = $request->input('session_token');
        if ($this->sessionService->maintain($sessionToken)) {
            try {
                $newToken = $this->authService->refreshSessionToken($sessionToken);
                return response()->json([
                    'status' => 200,
                    'message' => 'Session has been successfully maintained.',
                    'new_token' => $newToken
                ], 200);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => $e->getMessage()
                ], 500);
            }
        }
        return response()->json(['message' => 'Session has expired or is invalid.'], 401);
    }
}
