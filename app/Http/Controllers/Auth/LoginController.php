<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Models\LoginAttempt;
use App\Models\User; // Added to use the User model for querying
use Illuminate\Support\Facades\Hash; // Added to use the Hash facade for password verification
use App\Http\Requests\LoginRequest;

class LoginController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // Validate the input to ensure that the email and password fields are not empty.
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Check the format of the email to ensure it is valid.
        if (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Invalid email format.'], 422);
        }

        // Query the "users" table to find a user with the matching email address.
        $user = User::where('email', $credentials['email'])->first();

        // If no user is found or the password does not match the "password_hash" in the database
        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            // Record the login attempt in the "login_attempts" table with the "user_id" (if available), current timestamp as "attempted_at", and "success" set to false.
            LoginAttempt::create([
                'user_id' => $user ? $user->id : null,
                'attempted_at' => now(),
                'success' => false,
            ]);

            // Return an error message indicating that the login has failed.
            return response()->json([
                'message' => 'These credentials do not match our records.'
            ], 401);
        }

        // If authentication is successful, proceed with the original logic
        if ($this->authService->attempt($credentials)) {
            // Authentication passed...
            // Return success response or redirect to intended location
        }

        // This part should not be reached if the above conditions are met, but it's here as a fallback
        return response()->json([
            'message' => 'An unexpected error occurred.'
        ], 500);
    }

    // Existing methods...
}
