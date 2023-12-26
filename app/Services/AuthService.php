<?php

namespace App\Services;

use App\Models\User;
use App\Models\LoginAttempt;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon; // Use Carbon for date operations

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

        // Verify the password against the password_hash column if it exists, otherwise use password
        $passwordColumn = isset($user->password_hash) ? 'password_hash' : 'password';
        if (!$user || !Hash::check($password, $user->{$passwordColumn})) {
            // Log the failed login attempt using the static method if available, otherwise create a new record
            if (method_exists(LoginAttempt::class, 'logAttempt')) {
                LoginAttempt::logAttempt($user ? $user->id : null, now(), false, 'failed');
            } else {
                LoginAttempt::create([
                    'user_id' => $user ? $user->id : null,
                    'attempted_at' => now(),
                    'success' => false,
                    'status' => 'failed' // Added status field from new code
                ]);
            }

            throw new Exception('Authentication failed.');
        }

        // Determine the session expiration period using Carbon
        $sessionExpiration = $rememberToken ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

        // Generate a session token
        $sessionToken = bin2hex(random_bytes(30));

        // Update the user's session information
        $user->session_token = $sessionToken;
        $user->session_expiration = $sessionExpiration;
        $user->save();

        // Log the successful login attempt using the static method if available, otherwise create a new record
        if (method_exists(LoginAttempt::class, 'logAttempt')) {
            LoginAttempt::logAttempt($user->id, now(), true, 'success');
        } else {
            LoginAttempt::create([
                'user_id' => $user->id,
                'attempted_at' => now(),
                'success' => true,
                'status' => 'success' // Added status field from new code
            ]);
        }

        // Return session information
        return [
            'session_token' => $sessionToken,
            'session_expiration' => $sessionExpiration->toDateTimeString(), // Use toDateTimeString() for consistency
        ];
    }

    // ... (other methods)
}
