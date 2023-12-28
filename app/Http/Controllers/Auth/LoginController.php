<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Session; // Import the Session model
use App\Services\RecaptchaService; // Import the RecaptchaService

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email does not exist.'], 400);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 401);
        }

        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
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

            // Return successful login response
            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'token' => $user->remember_token,
            ]);
        } else {
            // Return error response for unverified email
            return response()->json(['error' => 'Email has not been verified.'], 401);
        }
    }

    /**
     * Cancel the logout process by keeping the session active.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelLogout(Request $request)
    {
        // Retrieve the session_token from the request body
        $sessionToken = $request->input('session_token');

        // Validate the session_token to ensure it is not empty
        if (empty($sessionToken)) {
            return response()->json(['error' => 'Session token is required.'], 401);
        }

        // Query the Session model using the session_token to find the active session
        $session = Session::where('session_token', $sessionToken)->where('is_active', true)->first();

        // If the session is found and is active, update the is_active attribute to keep the session active
        if ($session) {
            $session->is_active = true; // This might be redundant if the session is already active, but it's here for clarity
            $session->save();

            // Return a 200 Success response with the message "Logout cancelled successfully."
            return response()->json(['status' => 200, 'message' => 'Logout cancelled successfully.'], 200);
        }

        // If the session is not found or is not active, return a 401 Unauthorized response with an error message
        return response()->json(['error' => 'Session not found or inactive.'], 401);
    }
}
