<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $validated = $request->validated();

        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember_token');

        // Check the format of the email to ensure it is valid.
        if (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Invalid email format.'], 422);
        }

        // Query the "users" table to find a user with the matching email address.
        $user = User::where('email', $credentials['email'])->first();

        // If a user is found and the password is correct
        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Use the AuthService to attempt to log the user in
            if ($this->authService->attempt($credentials)) {
                // Generate session token and calculate expiration
                $sessionToken = Str::random(60);
                $sessionExpiration = $remember ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

                // Update user with new session token and expiration
                $user->update([
                    'session_token' => $sessionToken,
                    'session_expiration' => $sessionExpiration,
                ]);

                // Record the login attempt in the "login_attempts" table with the "user_id", current timestamp as "attempted_at", and "success" set to true.
                LoginAttempt::create([
                    'user_id' => $user->id,
                    'attempted_at' => Carbon::now(),
                    'success' => true,
                ]);

                // Return the "session_token" to the client to maintain the user's session.
                return response()->json([
                    'session_token' => $sessionToken,
                    'session_expiration' => $sessionExpiration,
                ]);
            }
        }

        // If the user is not found or the password is incorrect, log the failed login attempt
        LoginAttempt::create([
            'user_id' => $user ? $user->id : null,
            'attempted_at' => Carbon::now(),
            'success' => false,
        ]);

        // Return an error message indicating that the login has failed.
        return response()->json([
            'message' => 'These credentials do not match our records.'
        ], 401);
    }

    public function cancelLogin()
    {
        // Return a confirmation message with "login_canceled" set to true
        return response()->json(['login_canceled' => true], 200);
    }

    // Existing methods...
}
