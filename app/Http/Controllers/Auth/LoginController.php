<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
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

    protected function attemptLogin(array $credentials, $remember)
    {
        if (empty($credentials['email']) || empty($credentials['password'])) {
            Log::warning('Login attempt failed due to empty email or password.');
            return false;
        }

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $remember)) {
            $this->handleUserSession(Auth::user(), $remember);
            return true;
        }

        // Log the failed login attempt
        Log::warning('Login attempt failed for email: ' . $credentials['email']);
        return false;
    }

    protected function handleUserSession(User $user, $remember)
    {
        $expirationTime = $remember ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);
        $sessionToken = bin2hex(openssl_random_pseudo_bytes(30)); // Use the new code's method for generating a session token

        $session = Session::updateOrCreate(
            ['user_id' => $user->id],
            [
                'session_token' => $sessionToken,
                'expires_at' => $expirationTime // Use expires_at from new code
            ]
        );

        $user->session_token = $sessionToken;
        $user->save();
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();
        $remember = $request->input('remember', false); // Updated to use 'remember' instead of 'remember_token'

        if ($this->attemptLogin($credentials, $remember)) {
            $user = Auth::user();
            $sessionToken = $user->session_token;
            $session = Session::where('user_id', $user->id)->first();

            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'session_token' => $sessionToken,
                'session_expiration' => $session->expires_at->toIso8601String(),
            ]);
        }

        return $this->handleLoginFailure($request);
    }

    public function handleLoginFailure(Request $request)
    {
        // Check if the email and password fields are not empty
        if (empty($request->email) || empty($request->password)) {
            Log::warning('Login attempt failed due to empty email or password.');
            return response()->json(['error' => 'Login failed. The email or password you entered is incorrect.'], 401);
        }

        // Attempt to authenticate the user
        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password)) {
            // Log the failed login attempt
            Log::warning('Login attempt failed for email: ' . $request->email);

            // Return the error response
            return response()->json(['error' => 'Login failed. The email or password you entered is incorrect.'], 401);
        }

        // This point should not be reached if the credentials are incorrect, but it's here as a fallback
        return response()->json(['error' => 'Login failed. Incorrect email or password.'], 401);
    }

    // ... Rest of the existing code in the LoginController

    public function cancelLogin()
    {
        // Set a flash message to inform the user that the login process has been canceled
        session()->flash('message', 'Login process has been canceled.');

        // Redirect the user back to the previous screen or a designated route
        return redirect()->back();
    }
}
