<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoutRequest;
use App\Services\SessionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LogoutController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function logout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid session token.'], 400);
        }

        $sessionToken = $request->input('session_token');

        if ($this->sessionService->deleteSession($sessionToken)) {
            return response()->json(['status' => 200, 'message' => 'Successfully logged out.'], 200);
        }

        return response()->json(['message' => 'Unauthorized - Session token is invalid or user is not authenticated.'], 401);
    }
}
