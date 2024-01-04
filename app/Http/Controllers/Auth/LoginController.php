<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\RecaptchaService;

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
            return response()->json(['error' => 'Email address not found.'], 400);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 401);
        }

        if (!RecaptchaService::verify($request->input('recaptcha'))) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        if ($user->email_verified_at === null) {
            return response()->json(['error' => 'Email has not been verified.'], 401);
        }

        if (Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::user();
            $token = Str::random(60);
            $user->forceFill([
                'session_token' => $token,
                'is_logged_in' => true,
                'session_expiration' => now()->addMinutes(config('session.lifetime')),
                'updated_at' => now(),
            ])->save();

            LoginAttempt::create([
                'user_id' => $user->id,
                'attempted_at' => now(),
                'successful' => true,
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Login successful.',
                'session_token' => $token,
            ]);
        }

        return response()->json(['error' => 'Authentication failed.'], 401);
    }

    // ... rest of the existing methods ...
}
