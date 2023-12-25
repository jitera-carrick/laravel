<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class LoginController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $keepSession = $request->input('keep_session', false);

        // Validate the input
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid input', 'errors' => $validator->errors()], 422);
        }

        try {
            $user = $this->authService->validateUserCredentials($credentials['email'], $credentials['password']);

            if (!$user) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // Calculate session expiry time
            $sessionExpires = $keepSession ? now()->addDays(90) : now()->addHours(24);

            // Update user's session token and expiry
            $sessionToken = $this->authService->generateSessionToken($user);
            $user->update([
                'session_token' => $sessionToken,
                'session_expires' => $sessionExpires,
            ]);

            return response()->json([
                'session_token' => $sessionToken,
                'session_expires' => $sessionExpires,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Authentication failed'], 500);
        }
    }

    // ... other methods ...
}
