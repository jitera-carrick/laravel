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

        // Step 1: Update the $credentials array to include the 'remember_token' field if provided in the request.
        // The 'remember_token' field is not used in the login process, so we will not include it in the credentials.
        // Instead, we will use the 'remember' variable to determine if the user wants to be remembered.
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember_token');

        // Step 3: Check the format of the email to ensure it is valid.
        if (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Invalid email format.'], 422);
        }

        // Step 4: Query the "users" table to find a user with the matching email address.
        $user = User::where('email', $credentials['email'])->first();

        // Step 5: If a user is found, verify the password by comparing the provided password with the "password_hash" in the database.
        // The 'password_hash' field does not exist in the default Laravel User model, so we will use the 'password' field instead.
        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Use the AuthService to attempt to log the user in
            if ($this->authService->attempt($credentials)) {
                // Step 7 & 8: Generate session token and calculate expiration based on "Keep Session"
                $sessionToken = Str::random(60);
                $sessionExpiration = $remember ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

                // Step 9: Update user with new session token and expiration
                $user->update([
                    'session_token' => $sessionToken,
                    'session_expiration' => $sessionExpiration,
                ]);

                // Step 10: Record the login attempt in the "login_attempts" table with the "user_id", current timestamp as "attempted_at", and "success" set to true.
                LoginAttempt::create([
                    'user_id' => $user->id,
                    'attempted_at' => Carbon::now(),
                    'success' => true,
                ]);

                // Step 11: Return the "session_token" and "session_expiration" to the client
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
        // Check if the user is currently in the process of logging in
        if (Auth::check()) {
            // Log the user out to cancel the login process
            Auth::logout();
        }

        // Return a confirmation message with "login_canceled" set to true
        // The response structure has been updated to include both 'message' and 'login_canceled' to satisfy both new and existing code requirements.
        return response()->json(['message' => 'Login process has been canceled successfully.', 'login_canceled' => true], 200);
    }

    // Existing methods...
}
