<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.email' => 'Invalid email address.',
            'password.required' => 'Incorrect password.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $firstError = $errors->first();
            $statusCode = 400;
            return response()->json(['error' => $firstError], $statusCode);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'Email does not exist.'], 400);
        }

        // Retrieve password_hash and password_salt, hash provided password
        // Assuming that the hashing mechanism uses the 'salt' option
        $hashedPassword = Hash::make($password, ['salt' => $user->password_salt]);

        // Compare hashed password with password_hash
        if (!Hash::check($hashedPassword, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 401);
        }

        if ($user->email_verified_at !== null) {
            $sessionToken = Str::random(60);
            $user->forceFill([
                'session_token' => $sessionToken,
                'session_expiration' => now()->addMinutes(config('auth.passwords.users.expire')),
                'is_logged_in' => true,
                'updated_at' => now(),
            ])->save();

            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'session_token' => $sessionToken,
            ]);
        } else {
            return response()->json(['error' => 'Email has not been verified.'], 401);
        }
    }

    // ... (rest of the code remains unchanged)
}
