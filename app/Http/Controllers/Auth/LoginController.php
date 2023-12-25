<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log; // Import the Log facade
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

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                // Log the failed login attempt
                Log::warning('Failed login attempt for email: ' . $credentials['email']);

                // Return a generic error response
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // Calculate session expiry time
            $sessionExpires = $keepSession ? now()->addDays(90) : now()->addHours(24);

            // Generate a new "session_token"
            $sessionToken = $this->authService->generateSessionToken($user);

            // Update the user's record with the new "session_token" and "session_expires"
            $user->update([
                'session_token' => $sessionToken,
                'session_expires' => $sessionExpires,
                // Removed the 'keep_session' field update as it is not a field in the users table
            ]);

            // Prepare the response data, ensuring sensitive information is not included
            $responseData = [
                'session_token' => $sessionToken,
                'session_expires' => $sessionExpires->toDateTimeString(),
                'user' => $user->makeHidden(['password', 'password_hash', 'remember_token', 'session_token'])->toArray(),
            ];

            return response()->json($responseData);
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Login exception: ' . $e->getMessage());

            return response()->json(['message' => 'Authentication failed', 'error' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...
}
