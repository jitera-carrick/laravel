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
     * Handle the login request.
     *
     * @param LoginRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $keepSession = $request->input('keep_session', false);

        // Validate the input data to ensure that the email and password fields are not empty.
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // If the user is found and the password is correct, handle the session.
        if ($this->attemptLogin($credentials, $keepSession)) {
            $user = Auth::user();

            // Redirect to screen-menu_user after successful login if it's a web request
            if ($request->wantsJson()) {
                // If the application uses API tokens, return the token in the response.
                $token = $user->createToken('authToken')->accessToken;

                return response()->json([
                    'message' => 'Login successful.',
                    'token' => $token
                ]);
            } else {
                return redirect()->intended('screen-menu_user')->with([
                    'session_token' => $user->session_token,
                    // 'session_expiration' => $user->session_expiration, // This line is no longer needed
                ]);
            }
        } else {
            return response()->json([
                'error' => 'Login failed. Please check your email and password and try again.'
            ], 401);
        }
    }

    /**
     * Maintain the user session based on the session token and keepSession flag.
     *
     * @param string $sessionToken
     * @param bool $keepSession
     * @return \Illuminate\Http\JsonResponse
     */
    public function maintainUserSession($sessionToken, $keepSession = false)
    {
        try {
            $session = Session::where('session_token', $sessionToken)->first();

            if (!$session) {
                return response()->json(['message' => 'Session not found.'], 404);
            }

            if ($session->expires_at->isPast()) {
                return response()->json(['message' => 'Session has expired.'], 401);
            }

            if ($keepSession) {
                $session->expires_at = Carbon::now()->addDays(90);
                $session->save();

                return response()->json(['message' => 'Session has been updated to keep active for 90 more days.']);
            }

            return response()->json(['message' => 'Session remains unchanged.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred while maintaining the session: ' . $e->getMessage()], 500);
        }
    }

    // ... Rest of the existing code in the LoginController
}
