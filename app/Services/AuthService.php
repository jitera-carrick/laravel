<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoginAttempt;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthService
{
    // ... (other methods)

    /**
     * Authenticate a user by email and password.
     *
     * @param string $email
     * @param string $password
     * @param string|null $rememberToken
     * @return array
     * @throws Exception
     */
    public function authenticate($email, $password, $rememberToken = null)
    {
        // Validate the input
        $validator = Validator::make([
            'email' => $email,
            'password' => $password
        ], [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: ' . $validator->errors()->first());
        }

        // Retrieve the user by email
        $user = User::where('email', $email)->first();

        // Verify the password
        if (!$user || !Hash::check($password, $user->password)) {
            throw new Exception('Authentication failed.');
        }

        // Check if the "Keep Session" option is selected
        $sessionExpiration = $rememberToken ? now()->addDays(90) : now()->addHours(24);

        // Generate a session token
        $sessionToken = bin2hex(random_bytes(30));

        // Update the user's session information
        $user->session_token = $sessionToken;
        $user->session_expiration = $sessionExpiration;
        $user->save();

        // Log the login attempt
        LoginAttempt::create([
            'user_id' => $user->id,
            'attempted_at' => now(),
            'success' => true
        ]);

        // Return session information
        return [
            'session_token' => $sessionToken,
            'session_expiration' => $sessionExpiration,
        ];
    }

    // ... (other methods)
}
