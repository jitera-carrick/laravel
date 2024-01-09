<?php

namespace App\Services;

use App\Models\User;
use App\Http\Helpers\SessionHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function attemptLogin(array $credentials)
    {
        // Validate the input
        if (empty($credentials['email'])) {
            throw new \InvalidArgumentException("Email is required.");
        }

        if (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid email format.");
        }

        if (empty($credentials['password'])) {
            throw new \InvalidArgumentException("Password is required.");
        }

        if (isset($credentials['keep_session']) && !is_bool($credentials['keep_session'])) {
            throw new \InvalidArgumentException("Keep Session must be a boolean.");
        }

        // Attempt to find the user by email
        $user = User::where('email', $credentials['email'])->firstOrFail();

        // Check if the password is correct
        if ($user && Hash::check($credentials['password'], $user->password_hash)) {
            // Generate a new session token
            $sessionToken = Str::random(60);
            // Calculate the session expiration based on the 'keep_session' parameter
            $sessionExpiration = $credentials['keep_session'] ?? false
                ? now()->addWeeks(2) // If 'keep_session' is true, set a longer duration
                : now()->addMinutes(config('session.lifetime')); // Otherwise use the default session lifetime

            // Update the user's session information
            $user->fill([
                'session_token' => $sessionToken,
                'session_expiration' => $sessionExpiration
            ])->save();

            // Return the session token and expiration
            return [
                'status' => 200,
                'message' => "Login successful.",
                'session_token' => $sessionToken,
                'session_expiration' => $sessionExpiration->toIso8601String()
            ];
        }

        // If the credentials are incorrect, throw an exception
        throw new \Exception("Login failed. Invalid credentials.");
    }
}
