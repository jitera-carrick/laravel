
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest; // Use the custom LoginRequest
use Illuminate\Support\Facades\Hash;
use App\Services\SessionService; // Import the SessionService
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
        $sessionService = new SessionService(); // Initialize the SessionService

        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        $email = $request->validated()['email'];
        $password = $request->validated()['password'];
        $user = User::where('email', $email)->first();

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

        if ($user->email_verified_at !== null) {
            // Generate new remember_token and update user
            $user->forceFill([
                'remember_token' => Str::random(60),
                'updated_at' => now(),
            ])->save();

            // Use SessionService to create a session
            $sessionToken = $sessionService->createSession($user->id);

            // Update the "last_login_at" field in the User model
            $user->updateLastLoginTimestamp();

            // Return successful login response with remember_token and session management
            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'remember_token' => $user->remember_token, // Include remember_token in the response
                'session_token' => $sessionToken,
                // 'session_expiry' => $sessionExpiry->toDateTimeString(), // This line is removed as the expiry is handled by the SessionService
            ]);
        } else {
            // Return error response for unverified email
            return response()->json(['error' => 'Email has not been verified.'], 401);
        }
    }

    public function logout(Request $request)
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
