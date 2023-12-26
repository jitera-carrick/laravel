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
        $credentials = $request->only('email', 'password');
        $keepSession = $request->input('keep_session', false);

        // Validate the input
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required',
            'keep_session' => 'sometimes|boolean', // Added validation rule for 'keep_session'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid input', 'errors' => $validator->errors()], 422);
        }

        try {
            // Use the User model to query the "users" table for a user with the matching email address
            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
                // Log the failed login attempt
                Log::warning('Failed login attempt for email: ' . $credentials['email']);

                // Return a generic error response
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // Generate a new "session_token"
            $sessionToken = $this->authService->generateSessionToken($user);

            // Calculate the "session_expires" value based on the "keep_session" input
            $sessionExpires = $keepSession ? now()->addDays(90) : now()->addHours(24);

            // Update the user's record with the new "session_token" and "session_expires"
            $user->update([
                'session_token' => $sessionToken,
                'session_expires' => $sessionExpires,
                'keep_session' => $keepSession, // This line is new and assumes there is a 'keep_session' column in the 'users' table
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

    public function cancelLogin(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if ($user && $user->session_token && $user->session_expires > now()) {
                // Cancel the ongoing login attempt
                $user->update([
                    'session_token' => null,
                    'session_expires' => null,
                ]);

                return response()->json(['message' => 'Login process has been canceled successfully.'], 200);
            } else {
                return response()->json(['message' => 'No ongoing login process to cancel.'], 200);
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Cancel login exception: ' . $e->getMessage());

            return response()->json(['message' => 'Failed to cancel login process'], 500);
        }
    }

    public function editUserProfile(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($request->id),
            ],
            'password' => 'required|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        try {
            $user = $this->authService->getUserById($request->id);

            if ($request->email !== $user->email && $validator->sometimes('email', 'unique:users,email', function ($input) use ($user) {
                return $input->email !== $user->email;
            })->validate()) {
                $user->email = $request->email;
            }

            $user->password_hash = Hash::make($request->password);
            $user->save();

            // Update the "updated_at" column with the current timestamp
            $user->touch();

            return response()->json(['message' => 'User profile updated successfully'], 200);
        } catch (\Exception $e) {
            Log::error('User profile update exception: ' . $e->getMessage());

            return response()->json(['message' => 'Failed to update user profile', 'error' => $e->getMessage()], 500);
        }
    }

    // ... other methods ...
}
