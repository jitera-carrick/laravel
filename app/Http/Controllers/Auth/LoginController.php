
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
            // Retrieve the "session_token" from the request header or body
            $sessionToken = $request->header('session_token') ?? $request->input('session_token');
            // Find the user with the matching "session_token"
            $user = User::where('session_token', $sessionToken)->first();

            if ($user) {
                // Clear the "session_token" field, set "is_logged_in" to false, and update the "session_expiration"
                $user->update([
                    'session_token' => null,
                    'is_logged_in' => false, // Set "is_logged_in" to false
                    'session_expiration' => now(), // Update "session_expiration" to the current datetime
                ]);

                // Return a success response indicating the user has been successfully logged out
                return response()->json([
                    'message' => 'Logout successful.'
                ], 200);
            } else {
                // Return an error response if no user is found with the provided "session_token"
                return response()->json([
                    'error' => 'Logout failure.'
                ], 400);
            }
        } catch (\Exception $e) {
            // Handle any exceptions and return an appropriate error message
            return response()->json([
                'error' => 'An error occurred during logout.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
