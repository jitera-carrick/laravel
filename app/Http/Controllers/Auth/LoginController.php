<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\User;

class LoginController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(Request $request): JsonResponse
    {
        // Validate the input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'keep_session' => 'sometimes|boolean',
        ], [
            'email.email' => 'Invalid email format.',
            'password.required' => 'Password is required.',
            'keep_session.boolean' => 'Keep session must be a boolean.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid input', 'errors' => $validator->errors()], 422);
        }

        try {
            // Use the User model to query the "users" table for a user with the matching email address
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password_hash)) {
                // Log the failed login attempt
                Log::warning('Failed login attempt for email: ' . $request->email);

                // Return a generic error response
                return response()->json(['message' => 'The provided credentials do not match our records.'], 401);
            }

            // Generate a new "session_token"
            $sessionToken = $this->authService->generateSessionToken($user);

            // Calculate the "session_expires" value based on the "keep_session" input
            $sessionExpires = $request->input('keep_session', false) ? now()->addDays(90) : now()->addHours(24);

            // Update the user's record with the new "session_token" and "session_expires"
            $user->update([
                'session_token' => $sessionToken,
                'session_expires' => $sessionExpires,
                'keep_session' => $request->input('keep_session', false),
            ]);

            // Prepare the response data, ensuring sensitive information is not included
            $responseData = [
                'status' => 200,
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
