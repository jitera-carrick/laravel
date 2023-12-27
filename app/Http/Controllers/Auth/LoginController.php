<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest; // Keep this from the existing code
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    // ... Other methods in the LoginController

    // Combined attemptLogin method with logging from existing code
    protected function attemptLogin(array $credentials, $remember)
    {
        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']])) {
            $this->handleUserSession(Auth::user(), $remember);
            return true;
        }

        // Log the failed login attempt from existing code
        Log::warning('Login attempt failed for email: ' . $credentials['email']);

        return false;
    }

    // Combined handleUserSession method with logic from both new and existing code
    protected function handleUserSession(User $user, $remember)
    {
        // Determine the session_expiration time based on the 'remember' parameter
        $expirationTime = $remember ? Carbon::now()->addWeeks(2) : Carbon::now()->addHours(24);
        $sessionToken = bin2hex(openssl_random_pseudo_bytes(30)); // Use the new code's method for generating a session token

        // Create or update the session in the "sessions" table
        $session = Session::updateOrCreate(
            ['user_id' => $user->id],
            [
                'session_token' => $sessionToken,
                'expires_at' => $expirationTime // Use expires_at from new code
            ]
        );

        // Update the user's session_token attribute
        $user->session_token = $sessionToken;
        $user->save();
    }

    /**
     * Handle the login request with updated validation and response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $responseErrors = [];
            if ($errors->has('email')) {
                $responseErrors['email'] = "Invalid email format.";
            }
            if ($errors->has('password')) {
                $responseErrors['password'] = "Password is required.";
            }
            if ($errors->has('remember')) {
                $responseErrors['remember'] = "Invalid value for remember.";
            }
            return response()->json(['errors' => $responseErrors], 422);
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->input('remember', false);

        if ($this->attemptLogin($credentials, $remember)) {
            $user = Auth::user();
            $sessionToken = $user->session_token;
            $expiration = $remember ? Carbon::now()->addWeeks(2) : Carbon::now()->addHours(24);

            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'session_token' => $sessionToken,
                'session_expiration' => $expiration->toIso8601String(),
            ]);
        }

        // Handle failed authentication
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // ... Rest of the existing code in the LoginController

    // Maintain the maintainUserSession method from the existing code
    // Maintain the handleLoginFailure method from the existing code
}
