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
use Illuminate\Support\Str;

class LoginController extends Controller
{
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
     * Handle the API login request.
     *
     * @param \App\Http\Requests\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiLogin(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->input('remember', false);

        if ($this->attemptLogin($credentials, $remember)) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->accessToken;
            $expiration = $remember ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'session_token' => $token,
                'session_expiration' => $expiration->toIso8601String()
            ]);
        } else {
            return $this->handleLoginFailure($request);
        }
    }

    /**
     * Handle the login request.
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $keepSession = $request->input('keep_session', false);

        if ($this->attemptLogin($credentials, $keepSession)) {
            $user = Auth::user();

            if ($request->wantsJson()) {
                $token = $user->createToken('authToken')->accessToken;

                return response()->json([
                    'message' => 'Login successful.',
                    'token' => $token
                ]);
            } else {
                return redirect()->intended('screen-menu_user')->with([
                    'session_token' => $user->session_token,
                ]);
            }
        } else {
            return $this->handleLoginFailure($request);
        }
    }

    /**
     * Handle login failures by providing an appropriate error message.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleLoginFailure(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Account not found.'
            ], 401);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'status' => 401,
                'message' => 'Incorrect password.'
            ], 401);
        }

        return response()->json([
            'status' => 500,
            'message' => 'An unexpected error occurred on the server.'
        ], 500);
    }

    /**
     * Maintain the user session based on the session token and keepSession flag.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function maintainUserSession(Request $request)
    {
        $sessionToken = $request->input('session_token');
        $keepSession = $request->input('keep_session', false);

        // Validate the "session_token" and "keep_session" parameters
        $request->validate([
            'session_token' => 'required|exists:sessions,session_token',
            'keep_session' => 'required|boolean',
        ]);

        try {
            $session = Session::where('session_token', $sessionToken)->first();

            if (!$session) {
                return response()->json(['message' => 'Invalid session token.'], 400);
            }

            if ($session->expires_at->isPast()) {
                return response()->json(['message' => 'Session has expired.'], 401);
            }

            if ($keepSession) {
                $session->expires_at = Carbon::now()->addDays(90);
                $session->save();

                return response()->json([
                    'status' => 200,
                    'message' => 'Session maintained successfully.',
                    'session_expiration' => $session->expires_at->toIso8601String()
                ]);
            }

            return response()->json(['message' => 'Session remains unchanged.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while maintaining the session: ' . $e->getMessage()], 500);
        }
    }

    public function cancelLoginProcess()
    {
        // ... Existing cancelLoginProcess method code
    }

    // ... Rest of the existing code in the LoginController
}
