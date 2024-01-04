<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Database\Eloquent\ModelNotFoundException;
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
use App\Services\AuthService; // Import the AuthService
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
            $errors = $validator->errors();
            $responseErrors = [];
            if ($errors->has('email')) {
                $responseErrors['email'] = 'Please enter a valid email address.';
            }
            if ($errors->has('password')) {
                $responseErrors['password'] = 'Password cannot be blank.';
            }
            if ($errors->has('recaptcha')) {
                $responseErrors['recaptcha'] = 'Invalid recaptcha.';
            }
            return response()->json($responseErrors, 422);
        }

        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['recaptcha' => 'Invalid recaptcha.'], 401);
        }

        $user = User::where('email', $request->input('email'))->first();

        if (!$user) {
            return response()->json(['error' => 'User does not exist.'], 404);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials) || (method_exists($this, 'hashPassword') && Hash::check($this->hashPassword($request->input('password'), $user->password_salt), $user->password_hash))) {
            // Record successful login attempt
            LoginAttempt::create([
                'user_id' => $user->id,
                'attempted_at' => now(),
                'successful' => true,
                'ip_address' => $request->ip(),
            ]);

            if ($user->email_verified_at !== null) {
                $token = AuthService::updateUserLoginStatus($user);
                $user->session_token = $token;
                $user->is_logged_in = true;
                $user->session_expiration = Carbon::now()->addHours(2); // Session expires in 2 hours
                $user->save();

                return response()->json([
                    'status' => 200,
                    'session_token' => $token,
                    'user' => $user,
                ]);
            } else {
                return response()->json(['error' => 'Email has not been verified.'], 401);
            }
        } else {
            // Record failed login attempt
            LoginAttempt::create([
                'user_id' => null,
                'attempted_at' => now(),
                'successful' => false,
                'ip_address' => $request->ip(),
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    private function hashPassword($password, $salt)
    {
        return hash('sha256', $salt . $password);
    }

    public function logout(Request $request)
    {
        try {
            $sessionToken = $request->input('session_token') ?? $request->header('session_token');

            if (!$sessionToken) {
                throw new ModelNotFoundException('Session token is required.');
            }

            $user = User::findBySessionToken($sessionToken);

            if ($user) {
                $user->clearSession();

                return response()->json([
                    'status' => 200,
                    'message' => 'You have been logged out successfully.'
                ]);
            } else {
                throw new ModelNotFoundException('User not found with the provided session token.');
            }
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 404,
                'message' => 'User not found.',
                'error' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during logout.',
                'error' => $e->getMessage()
            ]);
        }
    }

    // ... other methods ...
}
