<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    // ... (rest of the existing code)

    public function login(LoginRequest $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            $user = User::where('email', $credentials['email'])->first();

            if (!$user) {
                return response()->json(['error' => 'Authentication failed.'], 401);
            }

            // Validate the email format and check if the email exists in the "users" table.
            if (!filter_var($credentials['email'], FILTER_VALIDATE_EMAIL)) {
                return response()->json(['error' => 'Invalid email format.'], 401);
            }

            // Retrieve the user's "password_hash" and "password_salt" from the database.
            $passwordHash = $user->password_hash;
            $passwordSalt = $user->password_salt;

            // Hash the input password with the retrieved "password_salt" and compare it with the "password_hash".
            if (!Hash::check($credentials['password'] . $passwordSalt, $passwordHash)) {
                return response()->json(['error' => 'Authentication failed.'], 401);
            }

            // Generate a new "session_token" and update the "session_token" and "session_expiration" in the "users" table.
            $sessionToken = $user->createSessionToken();
            $user->session_token = $sessionToken;
            $user->session_expiration = now()->addMinutes(config('session.lifetime'));
            $user->is_logged_in = true;
            $user->save();

            return response()->json([
                'message' => 'Login successful.',
                'session_token' => $sessionToken
            ], 200);

        } catch (\Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred during the login process.'], 500);
        }
    }

    // ... (rest of the existing code)
}
