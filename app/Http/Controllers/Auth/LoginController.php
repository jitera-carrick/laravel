
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
use Illuminate\Support\Facades\Auth; // Added Auth facade
use App\Models\Session;
use App\Services\RecaptchaService; // Import the RecaptchaService
use Carbon\Carbon; // Import Carbon for date manipulation

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recaptcha' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (RecaptchaService::enabled() && !RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        // New authentication logic starts here
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ], $request->all());

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            $user->session_token = Str::random(60); // Generate a new session token
            $expirationDays = $request->input('keep_session') ? 90 : 1;
            $user->session_expiration = now()->addDays($expirationDays);
            $user->save();
            
            // Record successful login attempt
            LoginAttempt::create([
                'user_id' => $user->id,
                'attempted_at' => now(),
                'successful' => true,
                'ip_address' => $request->ip(),
            ]);
            
            if ($user->email_verified_at !== null) {
                return response()->json([
                    'session_token' => $user->session_token,
                    'session_expiration' => $user->session_expiration,
                ]);
            } else {
                return response()->json(['error' => 'Email has not been verified.'], 401);
            } 
        }

        return response()->json(['error' => 'Unauthorized'], 401);
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
}
