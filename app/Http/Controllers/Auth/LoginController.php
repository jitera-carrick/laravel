<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();

        if ($user && Hash::check($password, $user->password)) {
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
                    'user_id' => $user->id,
                    'session_token' => $user->remember_token,
                ]);
            } else {
                // Return error response for unverified email
                return response()->json(['error' => 'Email has not been verified.'], 401);
            }
        } else {
            // Record failed login attempt
            LoginAttempt::create([
                'user_id' => $user ? $user->id : null,
                'attempted_at' => now(),
                'successful' => false,
                'ip_address' => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid credentials.'], 401);
        }
    }
}
