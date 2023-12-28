<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Str;
use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    // Existing methods...

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'recaptcha' => 'required|string',
        ]);

        // Validate the recaptcha response with an external service
        $recaptchaValid = $this->validateRecaptcha($credentials['recaptcha']);
        if (!$recaptchaValid) {
            return response()->json(['error' => 'Invalid recaptcha.'], 422);
        }

        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json(['error' => 'Email does not exist.'], 401);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Invalid password.'], 401);
        }

        if ($user->email_verified_at === null) {
            return response()->json(['error' => 'Email not verified.'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->update(['remember_token' => Str::random(60), 'updated_at' => now()]);

        LoginAttempt::create([
            'user_id' => $user->id,
            'attempted_at' => now(),
            'successful' => true,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Login successful.',
            'access_token' => $token
        ]);
    }

    protected function validateRecaptcha($recaptchaResponse)
    {
        $secretKey = config('services.recaptcha.secret'); // Assuming you have the secret key in your config/services.php
        $response = Http::post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $recaptchaResponse,
        ]);

        return $response->json()['success'] ?? false;
    }

    // Other methods...
}
