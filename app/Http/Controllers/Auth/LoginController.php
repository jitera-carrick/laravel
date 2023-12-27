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

    // Combined attemptLogin method with logging from existing code
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

    // Combined handleUserSession method with logic from both new and existing code
    protected function handleUserSession(User $user, $remember)
    {
        // Determine the session_expiration time based on the 'remember' parameter
        $expirationTime = $remember ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);
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

    // ... Rest of the existing code in the LoginController

    // New handleLoginFailure method as per the guideline
    /**
     * Handle the login failure response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleLoginFailure(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Account not found.'], 401);
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(['message' => 'Incorrect password.'], 401);
        }

        // This point should not be reached if the credentials are incorrect, but it's here as a fallback
        return response()->json(['message' => 'Login failed. Incorrect email or password.'], 401);
    }

    // ... Rest of the existing code in the LoginController

    /**
     * Cancel the login process and redirect back to the previous screen.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancelLogin()
    {
        // Set a flash message to inform the user that the login process has been canceled
        session()->flash('message', 'Login process has been canceled.');

        // Redirect the user back to the previous screen or a designated route
        return redirect()->back();
    }
}
