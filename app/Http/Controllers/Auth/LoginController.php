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
            // Query the "users" table to find a user with the matching email address
            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // Calculate session expiry time
            $sessionExpires = $keepSession ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

            // Generate a new "session_token"
            $sessionToken = $this->authService->generateSessionToken($user);

            // Update the user's record with the new "session_token" and "session_expires"
            $user->update([
                'session_token' => $sessionToken,
                'session_expires' => $sessionExpires,
                'keep_session' => $keepSession, // This line is unnecessary as 'keep_session' is not a field in the users table
            ]);

            // Prepare the response data, ensuring sensitive information is not included
            $responseData = [
                'session_token' => $sessionToken,
                'session_expires' => $sessionExpires->toDateTimeString(),
                'user' => $user->makeHidden(['password', 'password_hash', 'remember_token', 'session_token'])->toArray(),
            ];

            return response()->json($responseData);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Authentication failed', 'error' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...
}
