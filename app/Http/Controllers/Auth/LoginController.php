
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;

use App\Models\Session;
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

    public function logout(Request $request)
    {
        try {
            // Update the method to receive the session token from the request body instead of a cookie
            $sessionToken = $request->input('session_token');
            // Query the "users" table using the Eloquent model `User` to find a record with the matching "session_token"
            $user = User::where('session_token', $sessionToken)->first();

            if ($user) {
                // Update the "is_logged_in" field to false and clear the "session_token" and "session_expiration" fields in the user's record
                $user->is_logged_in = false;
                $user->session_token = null;
                $user->session_expiration = null;
                $user->save();

                // Return a success response indicating the user has been logged out
                return response()->json([
                    'status' => 200,
                    'message' => 'Logout successful.'
                ]);
            }

            // If no matching session token is found, return an error response indicating the logout failed
            return response()->json([
                'status' => 400,
                'message' => 'Logout failed. No matching session token found.'
            ]);

            // The previous code for handling sessions and cookies is no longer needed and has been removed

        } catch (\Exception $e) {
            // Handle any exceptions that may occur during the logout process and return an appropriate error response
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during logout.',
                'error' => $e->getMessage()
            ]);
        }
    }
}
