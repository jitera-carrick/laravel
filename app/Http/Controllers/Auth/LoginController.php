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
use Illuminate\Validation\ValidationException;

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

        // Merged Step 1: Update the $credentials array to include the 'remember_token' field if provided in the request.
        // The 'remember_token' field is not used in the new code, so it's safe to remove it from the credentials array.
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember_token');

        // Merged Step 3: Check the format of the email to ensure it is valid.
        if (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Invalid email format.'], 422);
        }

        // Merged Step 4: Query the "users" table to find a user with the matching email address.
        $user = User::where('email', $credentials['email'])->first();

        // Merged Step 5: If a user is found, verify the password by comparing the provided password with the "password_hash" in the database.
        // Note: The "password_hash" column should exist in the "users" table. If it doesn't, you should use the existing "password" column.
        // The new code does not mention a "password_hash" column, so we will use the "password" column as in the new code.
        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Use the AuthService to attempt to log the user in
            if ($this->authService->attempt($credentials)) {
                // Merged Step 7 & 8: Generate session token and calculate expiration based on "Keep Session"
                $sessionToken = Str::random(60);
                $sessionExpiration = $remember ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

                // Merged Step 9: Update user with new session token and expiration
                $user->update([
                    'session_token' => $sessionToken,
                    'session_expiration' => $sessionExpiration,
                ]);

                // Merged Step 10: Record the login attempt in the "login_attempts" table with the "user_id", current timestamp as "attempted_at", and "success" set to true.
                LoginAttempt::create([
                    'user_id' => $user->id,
                    'attempted_at' => Carbon::now(),
                    'success' => true,
                ]);

                // Merged Step 11: Return the "session_token" and "session_expiration" to the client
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

        // Merged: Return a confirmation message with additional 'login_canceled' key from the existing code
        return response()->json(['message' => 'Login process has been canceled successfully.', 'login_canceled' => true], 200);
    }

    public function maintainSession(Request $request)
    {
        // Validate the input to ensure that the 'email' field is not empty
        $validatedData = $request->validate([
            'email' => 'required|email',
            'remember_token' => 'sometimes|required',
        ]);

        // Use the `User` model to query the "users" table for a user with the matching email address.
        $user = User::where('email', $validatedData['email'])->first();

        // Initialize the response data
        $responseData = ['session_maintained' => false];

        // If a user is found and the "remember_token" is provided and matches the user's remember_token
        if ($user && isset($validatedData['remember_token']) && $validatedData['remember_token'] === $user->remember_token) {
            // Update the "session_expiration" in the "users" table to extend the session by a predefined duration, such as 90 days.
            $user->session_expiration = Carbon::now()->addDays(90);
            $user->save();

            // Set the response data indicating the session was extended
            $responseData['session_maintained'] = true;
        }

        // Return a JSON response with a "session_maintained" boolean key
        return response()->json($responseData);
    }

    // Existing methods...
}
