<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Models\Session;
use App\Services\RecaptchaService; // Import the RecaptchaService
use App\Services\SessionService; // Import the SessionService
use Carbon\Carbon;

class LoginController extends Controller
{
    protected $sessionService;

    public function __construct(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha' => 'required|string',
            'keep_session' => 'sometimes|boolean' // Add validation for keep_session
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $responseErrors = [];
            if ($errors->has('email')) {
                $responseErrors['email'] = $errors->first('email') === 'The email field is required.' ? 'Email is required.' : 'Invalid email format.';
            }
            if ($errors->has('password')) {
                $responseErrors['password'] = 'Password is required.';
            }
            if ($errors->has('keep_session')) {
                $responseErrors['keep_session'] = 'Keep session must be a boolean.';
            }
            return response()->json($responseErrors, 400);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $keepSession = $request->input('keep_session', false); // Default to false if not provided

        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

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

        // Generate new session token
        $sessionToken = Str::random(60);

        // Determine session expiration
        $sessionExpiration = $keepSession ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);

        // Update user's session token and expiration
        $user->forceFill([
            'session_token' => $sessionToken,
            'session_expiration' => $sessionExpiration,
            'updated_at' => now(),
        ])->save();

        // Return successful login response with session token and expiration
        return response()->json([
            'status' => 200,
            'message' => 'Login successful.',
            'session_token' => $sessionToken,
            'session_expiration' => $sessionExpiration->toIso8601String(),
        ]);
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
