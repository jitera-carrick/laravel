<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;

class LoginController extends Controller
{
    // Add the new attemptLogin method
    protected function attemptLogin(array $credentials, $keepSession)
    {
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $this->handleUserSession(Auth::user(), $keepSession);
            return true;
        }
        return false;
    }

    // Add the new handleUserSession method
    protected function handleUserSession(User $user, $keepSession)
    {
        // Determine the session_expiration time based on whether the keep_session flag is set
        $expirationTime = $keepSession ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);
        $sessionToken = bin2hex(openssl_random_pseudo_bytes(30)); // Generate a random session token

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

        // Query the "users" table to find a user with a matching email address.
        $user = User::where('email', $credentials['email'])->first();

        // If no user is found or the password does not match the password_hash stored in the database, return an error response.
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'error' => 'Login failed. Please check your email and password and try again.'
            ], 401);
        }

        // If the user is found and the password is correct, handle the session.
        if ($this->attemptLogin($credentials, $keepSession)) {
            // Redirect to screen-menu_user after successful login
            return redirect()->intended('screen-menu_user')->with([
                'session_token' => $user->session_token,
                'session_expiration' => $user->session_expiration,
            ]);
        } else {
            // If the application uses API tokens, return the token in the response.
            $token = $user->createToken('authToken')->accessToken;

            return response()->json([
                'message' => 'Login successful.',
                'token' => $token
            ]);
        }
    }

    // ... Rest of the existing code in the LoginController
}
