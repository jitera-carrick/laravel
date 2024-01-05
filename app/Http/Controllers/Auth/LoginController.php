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
use App\Helpers\HashHelper; // Assuming HashHelper exists

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

        // Check if the user has password_hash and password_salt fields for backward compatibility
        if (isset($user->password_hash) && isset($user->password_salt)) {
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
            // Use the default Laravel Hash check for users without password_hash and password_salt
            if (!Hash::check($password, $user->password)) {
                return response()->json(['error' => 'Incorrect password.'], 401);
            }
        }

        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        // Generate a new session_token and update user
        $sessionToken = Str::random(60);
        $user->forceFill([
            'session_token' => $sessionToken,
            'session_expiration' => now()->addHours(2),
            'is_logged_in' => true,
            'updated_at' => now(),
        ])->save();

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
            // Retrieve the session token from the request header, body, or cookie
            $sessionToken = $request->header('session_token');
            if (!$sessionToken) {
                $sessionToken = $request->input('session_token');
                if (!$sessionToken) {
                    $sessionToken = $request->cookie('session_token');
                }
            }

            $session = Session::where('session_token', $sessionToken)
                              ->where('is_active', true)
                              ->first();

            if ($session) {
                $user = $session->user;
                $user->is_logged_in = false;
                $user->save();

                $session->is_active = false;
                $session->save();
            } else {
                // Find the user with the matching session token for backward compatibility
                $user = User::where('session_token', $sessionToken)->first();
                if ($user) {
                    // Update the user's session information
                    $user->update(['session_token' => null, 'is_logged_in' => false, 'session_expiration' => now()]);
                } else {
                    throw new \Exception('Session token mismatch');
                }
            }

            Cookie::queue(Cookie::forget('session_token'));

            return response()->json([
                'status' => 200,
                'message' => 'Logout successful.'
            ]);
        } catch (\Exception $e) {
            $statusCode = $e->getMessage() === 'Session token mismatch' ? 400 : 500;
            return response()->json(
                ['message' => 'An error occurred during logout.', 'error' => $e->getMessage()],
                $statusCode
            );
        }
    }
}
