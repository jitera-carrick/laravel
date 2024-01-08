
<?php

namespace App\Http\Controllers\Auth;

use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest; // Use the custom LoginRequest
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Session;
use App\Services\RecaptchaService; // Import the RecaptchaService
use Carbon\Carbon; // Import Carbon for date handling

class LoginController extends Controller
{   
    public function login(LoginRequest $request)
    {
        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        $email = $request->validated()['email'];
        $password = $request->validated()['password'];
        $user = User::where('email', $email)->first();
        $token = null;

        if (!$user) {
            return response()->json(['error' => 'Email does not exist.'], 400);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 401);
        }

        // Record successful login attempt
        LoginAttempt::create([
            'user_id' => $user->id,
            'attempted_at' => now(),
            'successful' => true,
            'ip_address' => $request->ip(),
        ]);

        // Generate JWT token
        $token = JWTAuth::fromUser($user);

        // Return successful login response with JWT token
        return response()->json(['token' => $token]);

        if ($user->email_verified_at !== null) {
            // Generate new remember_token and update user
            $user->forceFill([
                'remember_token' => Str::random(60),
                'updated_at' => now(),
            ])->save();

            // New logic for session management
            $keepSession = $request->input('keep_session', false);
            $sessionExpiry = $keepSession ? Carbon::now()->addDays(90) : Carbon::now()->addDay();
            $sessionToken = Hash::make(Str::random(60));

            $user->forceFill([
                'session_token' => $sessionToken,
                'session_expiry' => $sessionExpiry,
                'updated_at' => now(),
            ])->save();

            // Return successful login response with remember_token and session management
            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'remember_token' => $user->remember_token, // Include remember_token in the response
                'session_token' => $sessionToken,
                'session_expiry' => $sessionExpiry->toDateTimeString(),
            ]);
        } else {
            // Return error response for unverified email
            return response()->json(['error' => 'Email has not been verified.'], 401);
        }
    }
    {
        try {
            $sessionToken = $request->cookie('session_token'); // Use the cookie method to retrieve the session token
            $session = Session::where('session_token', $sessionToken)
                              ->where('is_active', true)
                              ->first();

            if ($session) {
                $user = $session->user;
                $user->is_logged_in = false;
                $user->save();

                $session->is_active = false;
                $session->save();

                Cookie::queue(Cookie::forget('session_token'));

                return response()->json([
                    'status' => 200,
                    'message' => 'Logout successful.'
                ]);
            }

            return response()->json([
                'status' => 400,
                'message' => 'No active session found.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during logout.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cancelLogin()
    {
        // Check for an ongoing login process (e.g., a session variable)
        if (session()->has('login_in_progress')) {
            // Perform necessary cleanup to terminate the login process
            session()->forget('login_in_progress');
            // You may also need to perform other cleanup tasks depending on your application's logic

            // Return a confirmation message
            return response()->json(['message' => __('auth.cancel_confirmation_message')]);
        }

        // If there is no ongoing login process, return a message indicating that there is nothing to cancel
        return response()->json(['message' => __('auth.no_login_process')]);
    }
}
