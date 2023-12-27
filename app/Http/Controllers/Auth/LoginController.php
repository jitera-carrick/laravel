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
    // Existing attemptLogin method updated to include logging and response
    protected function attemptLogin(array $credentials, $keepSession)
    {
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $this->handleUserSession(Auth::user(), $keepSession);
            return true;
        }

        // Log the failed login attempt
        Log::warning('Login attempt failed for email: ' . $credentials['email']);

        return false;
    }

    // Existing handleUserSession method updated to include Session model logic
    protected function handleUserSession(User $user, $keepSession)
    {
        // Determine the session_expiration time based on whether the keep_session flag is set
        $expirationTime = $keepSession ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);
        $sessionToken = Str::random(60); // Generate a random session token using Str::random

        // Create a new entry in the "sessions" table or update the existing session
        $session = Session::updateOrCreate(
            ['user_id' => $user->id],
            [
                'session_token' => $sessionToken,
                'created_at' => Carbon::now(),
                'session_expiration' => $expirationTime,
            ]
        );

        // Update the "users" table, setting the "session_token" and "session_expiration" for the user
        $user->session_token = $sessionToken;
        $user->session_expiration = $expirationTime;
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
                return response()->json([
                    'message' => 'Login successful.',
                    'session_token' => $user->session_token,
                    'session_expiration' => $user->session_expiration->toDateTimeString(),
                ]);
            } else {
                return redirect()->intended('screen-menu_user')->with([
                    'session_token' => $user->session_token,
                    'session_expiration' => $user->session_expiration,
                ]);
            }
        } else {
            return response()->json([
                'error' => 'Login failed. Please check your email and password and try again.'
            ], 401);
        }
    }

    // ... Rest of the existing code in the LoginController
}
