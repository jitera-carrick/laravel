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

        // Ensure that the 'email' and 'password' fields are not empty
        if (empty($validated['email']) || empty($validated['password'])) {
            return response()->json(['message' => 'Email and password are required.'], 422);
        }

        // Check the format of the email to ensure it is valid.
        if (!filter_var($validated['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Invalid email format.'], 422);
        }

        // Query the "users" table to find a user with the matching email address.
        $user = User::where('email', $validated['email'])->first();

        // If a user is found, verify the password by comparing the provided password with the "password_hash" in the database.
        // Note: The "password_hash" column should exist in the "users" table. If it doesn't, you should use the existing "password" column.
        if ($user && Hash::check($validated['password'], $user->password)) {
            // Use the AuthService to attempt to log the user in
            if ($this->authService->attempt($validated)) {
                // Determine if "Keep Session" is selected
                $remember = $request->filled('remember_token');

                // Generate a session token
                $sessionToken = Str::random(60);
                // Set the expiration based on "Keep Session" selection
                $sessionExpiration = $remember ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

                // Update the user's 'session_token' and 'session_expiration'
                $user->update([
                    'session_token' => $sessionToken,
                    'session_expiration' => $sessionExpiration,
                ]);

                // Log the login attempt
                LoginAttempt::create([
                    'user_id' => $user->id,
                    'attempted_at' => Carbon::now(),
                    'success' => true,
                ]);

                // Return a JSON response with the 'session_token' and 'session_expiration'
                return response()->json([
                    'session_token' => $sessionToken,
                    'session_expiration' => $sessionExpiration,
                ]);
            }
        }

        // Log the failed attempt
        LoginAttempt::create([
            'user_id' => $user ? $user->id : null,
            'attempted_at' => Carbon::now(),
            'success' => false,
        ]);

        // Return a 401 response with an error message
        return response()->json(['message' => 'These credentials do not match our records.'], 401);
    }

    public function cancelLogin()
    {
        // Check if the user is currently in the process of logging in
        if (Auth::check()) {
            // Log the user out
            Auth::logout();
        }

        // Return a confirmation message
        return response()->json(['message' => 'Login process has been canceled successfully.', 'login_canceled' => true], 200);
    }

    public function maintainSession(Request $request)
    {
        // Validate the input to ensure that the 'email' field is not empty
        $validatedData = $request->validate([
            'email' => 'required|email',
            'remember_token' => 'sometimes|required|string',
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
