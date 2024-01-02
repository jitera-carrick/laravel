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
        $validator = Validator::make($request->all(), [
            'session_token' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $sessionToken = $request->input('session_token');
        $userId = $request->input('user_id');

        $session = Session::where('session_token', $sessionToken)
                          ->where('user_id', $userId) // Ensure the session belongs to the user
                          ->where('is_active', true)
                          ->first();

        if (!$session) {
            return response()->json(['error' => 'Invalid or inactive session.'], 400);
        }

        $session->is_active = false;
        $session->save();

        $user = User::find($userId);
        if ($user) {
            $user->is_logged_in = false;
            $user->session_token = null; // Remove the session_token from the user's record
            $user->save();
        }

        // If applicable, clear the session information from the server-side session storage or cache.
        // This part is dependent on the session management implementation which is not provided in the guideline.
        // Assuming there is a method to clear the session, it would be something like this:
        // SessionService::clearSession($sessionToken);

        // Clear the session token cookie
        Cookie::queue(Cookie::forget('session_token'));

        return response()->json([
            'status' => 200,
            'message' => 'Logout successful.'
        ]);
    }
}
