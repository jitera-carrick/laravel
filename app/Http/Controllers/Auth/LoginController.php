<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    // ... Other methods in the LoginController

    // Combined attemptLogin method with logging from existing code
    protected function attemptLogin(array $credentials, $keepSession)
    {
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $this->handleUserSession(Auth::user(), $keepSession);
            return true;
        }

        // Log the failed login attempt from existing code
        Log::warning('Login attempt failed for email: ' . $credentials['email']);

        return false;
    }

    // Combined handleUserSession method with logic from both new and existing code
    protected function handleUserSession(User $user, $keepSession)
    {
        // Determine the session_expiration time based on whether the keep_session flag is set
        $expirationTime = $keepSession ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);
        $sessionToken = bin2hex(openssl_random_pseudo_bytes(30)); // Use the new code's method for generating a session token

        // Create or update the session in the "sessions" table
        $session = Session::updateOrCreate(
            ['user_id' => $user->id],
            [
                'session_token' => $sessionToken,
                'expires_at' => $expirationTime // Use expires_at from new code
            ]
        );

        // Update the user's session_token attribute
        $user->session_token = $sessionToken;
        $user->save();
    }

    /**
     * Handle the login request.
     *
     * @param \App\Http\Requests\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $keepSession = $request->input('remember_token', false);

        if ($this->attemptLogin($credentials, $keepSession)) {
            $user = Auth::user();
            $sessionToken = $user->session_token;
            $expiration = $keepSession ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

            return response()->json([
                'session_token' => $sessionToken,
                'session_expiration' => $expiration->toIso8601String(),
            ]);
        }

        // Handle failed authentication
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    /**
     * Maintain the user session based on the user_id and session_token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function maintainUserSession(Request $request)
    {
        // Validate the request to ensure user_id is present
        $validator = Validator::make($request->all(), [
            'user_id' => 'sometimes|exists:users,id',
            'session_token' => 'required|exists:sessions,session_token',
            'keep_session' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            // Retrieve the session using the Session model and the provided user_id or session_token
            $query = Session::query();
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            $query->where('session_token', $request->session_token);
            $session = $query->first();

            if (!$session) {
                return response()->json(['message' => 'Session not found.'], 404);
            }

            if ($session->expires_at->isPast()) {
                return response()->json(['message' => 'Session has expired.'], 401);
            }

            // Calculate the new expiration date based on the keep_session attribute
            $newExpirationDate = $request->keep_session ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

            // Update the expires_at field in the session record
            $session->expires_at = $newExpirationDate;
            $session->save();

            return response()->json([
                'status' => 200,
                'message' => 'User session has been maintained.',
                'new_expiration' => $newExpirationDate->toIso8601String()
            ]);
        } catch (\Exception $e) {
            Log::error('Error maintaining user session: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while maintaining the session.'], 500);
        }
    }

    /**
     * Handle the login failure request.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleLoginFailure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Account not found.'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Incorrect password.'], 401);
        }

        // If the code reaches this point, it means there was an unexpected error
        Log::error('Unexpected error occurred during login failure handling.');
        return response()->json(['error' => 'An unexpected error occurred.'], 500);
    }

    // ... Rest of the existing code in the LoginController
}
