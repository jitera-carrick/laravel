<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use App\Utils\ApiResponse;
use Carbon\Carbon;
use App\Models\User;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $email = $validated['email'];
        $password = $validated['password'];

        $loginAttempt = $this->authService->attemptLogin($email, $password);

        if ($loginAttempt['success']) {
            return ApiResponse::success([
                'token' => $loginAttempt['token']
            ], 'Login successful.');
        }

        return ApiResponse::error('Login attempt was unsuccessful.', 401);
    }

    // ... other methods ...

    public function someMethod()
    {
        // Existing code...
    }

    public function logout(Request $request)
    {
        $sessionToken = $request->header('session_token') ?: $request->input('session_token');

        if (!$sessionToken) {
            return response()->json(['error' => 'Session token is required.'], 401);
        }

        $user = User::where('session_token', $sessionToken)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid session token.'], 401);
        }

        $user->update([
            'session_token' => null,
            'is_logged_in' => false,
            'session_expiration' => Carbon::now(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Logout successful.'
        ]);
    }
}
