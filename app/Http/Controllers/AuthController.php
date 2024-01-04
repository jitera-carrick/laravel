<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest; // Import the LoginRequest
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // ... existing login method code ...

    /**
     * Handle the logout process for a user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Validate the session token
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        // Retrieve the session token from the request
        $sessionToken = $request->input('session_token') ?: $request->header('session_token');

        try {
            // Find the user by session token
            $user = $this->authService->getUserBySessionToken($sessionToken);

            // If user not found, return unauthorized response
            if (!$user) {
                return response()->json(['message' => 'Invalid session token.'], 401);
            }

            // Invalidate the user session
            $this->authService->invalidateSession($user);

            // Return successful logout response
            return response()->json(['status' => 200, 'message' => 'Logout successful.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during the logout process.', 'error' => $e->getMessage()], 500);
        }
    }

    // ... other methods that might exist in the controller ...
}
