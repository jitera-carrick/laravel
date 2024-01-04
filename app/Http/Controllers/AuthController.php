<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // ... existing methods ...

    /**
     * Handle the logout process for a user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $sessionToken = $request->input('session_token');

        try {
            $user = $this->authService->getUserBySessionToken($sessionToken);

            if (!$user) {
                return response()->json(['message' => 'Invalid session token.'], 401);
            }

            $this->authService->invalidateSession($user);

            return response()->json(['status' => 200, 'message' => 'You have been logged out successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during the logout process.'], 500);
        }
    }

    // ... other methods ...
}
