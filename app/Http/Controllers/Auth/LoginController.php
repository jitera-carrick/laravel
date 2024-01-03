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
use Illuminate\Support\Facades\Log; // Import Log facade for logging exceptions

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
        $recaptchaToken = $request->input('recaptcha');

        // Verify the recaptcha token
        if (!RecaptchaService::verify($recaptchaToken)) {
            return response()->json(['error' => 'Invalid recaptcha.'], 401);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'Invalid email address.'], 401);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json(['error' => 'Incorrect password.'], 401);
        }

        // Generate an access token
        $token = $user->createToken('authToken')->plainTextToken;

        // Record successful login attempt
        LoginAttempt::create([
            'user_id' => $user->id,
            'attempted_at' => now(),
            'successful' => true,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'status' => 200,
            'access_token' => $token,
            'user' => $user
        ], 200);
    }

    public function logout(Request $request)
    {
        // ... (logout method remains unchanged)
    }

    // ... (rest of the code remains unchanged)
}
