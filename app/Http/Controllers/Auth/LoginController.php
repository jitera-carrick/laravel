<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    // Add the new login method below

    /**
     * Handle the login request.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // Validate the input to ensure that the "email" and "password" fields are not empty.
        // Check the format of the "email" to ensure it is a valid email address.
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        try {
            $user = User::where('email', $credentials['email'])->first();

            // If no user is found, return an error indicating that the login credentials are invalid.
            if (!$user) {
                return response()->json(['message' => 'Invalid credentials.'], 401);
            }

            // If a user is found, verify the "password" by comparing it with the encrypted password stored in the "users" table using a secure password hashing algorithm.
            if (Hash::check($credentials['password'], $user->password)) {
                // Log the successful login attempt
                LoginAttempt::create([
                    'user_id' => $user->id,
                    'attempted_at' => now(),
                    'successful' => true,
                    'ip_address' => $request->ip(),
                ]);

                // Generate a token for the user
                $token = $user->createToken('authToken')->plainTextToken;

                // Update the remember_token if needed
                if ($remember) {
                    $user->setRememberToken(Hash::make(Str::random(60)));
                    $user->save();
                }

                // Transform the user data using UserResource
                $userResource = new UserResource($user);

                return response()->json([
                    'message' => 'Login successful.',
                    'user' => $userResource,
                    'token' => $token,
                ]);
            } else {
                // If the password is incorrect, log the failed attempt in the "login_attempts" table with "successful" set to false, and return an error message.
                LoginAttempt::create([
                    'user_id' => $user->id,
                    'attempted_at' => now(),
                    'successful' => false,
                    'ip_address' => $request->ip(),
                ]);

                return response()->json(['message' => 'Invalid credentials.'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during the login process.'], 500);
        }
    }
}
