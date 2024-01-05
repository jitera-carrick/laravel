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
use App\Services\RecaptchaService;
use App\Helpers\HashHelper;
use Carbon\Carbon;

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

        // Check if the user has password_salt for backward compatibility
        if (isset($user->password_salt)) {
            // Retrieve password_hash and password_salt from the database
            $passwordHash = $user->password_hash;
            $passwordSalt = $user->password_salt;

            // Hash the provided password with the retrieved password_salt
            $hashedPassword = HashHelper::hashPassword($password, $passwordSalt);

            // Compare the hashed password with the password_hash from the database
            if ($hashedPassword !== $passwordHash) {
                return response()->json(['error' => 'Incorrect password.'], 401);
            }
        } else {
            // Use Laravel's default Hash facade for password verification
            if (!Hash::check($password, $user->password)) {
                return response()->json(['error' => 'Incorrect password.'], 401);
            }
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

        // Generate a new session_token and update user
        $user->forceFill([
            'session_token' => Str::random(60),
            'session_expiration' => now()->addHours(config('session.lifetime')),
            'is_logged_in' => true,
            'remember_token' => Str::random(60), // Added for new code compatibility
            'updated_at' => now(),
        ])->save();

        if ($user->email_verified_at !== null) {
            // Return successful login response
            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'token' => $user->session_token, // Use session_token for backward compatibility
                'remember_token' => $user->remember_token, // Added for new code compatibility
            ]);
        } else {
            // Return error response for unverified email
            return response()->json(['error' => 'Email has not been verified.'], 401);
        }
    }

    public function logout(Request $request)
    {
        try {
            // Check for session_token in header or body, then fallback to cookie
            $sessionToken = $request->header('session_token') ?? $request->input('session_token') ?? $request->cookie('session_token');
            $session = Session::where('session_token', $sessionToken)
                              ->where('is_active', true)
                              ->first();

            if ($session) {
                $user = $session->user;
                $user->is_logged_in = false;
                $user->session_token = null; // Added for new code compatibility
                $user->session_expiration = Carbon::now(); // Added for new code compatibility
                $user->save();

                $session->is_active = false; // Added for existing code compatibility
                $session->save(); // Added for existing code compatibility

                Cookie::queue(Cookie::forget('session_token')); // Added for existing code compatibility

                return response()->json([
                    'status' => 200,
                    'message' => 'Logout successful.'
                ]);
            } else {
                return response()->json([
                    'status' => 400,
                    'message' => 'Invalid session token.'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during logout.',
                'error' => $e->getMessage()
            ]);
        }
    }
}
