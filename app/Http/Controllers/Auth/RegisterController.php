<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\VerifyEmailNotification;
use App\Services\EmailService; // Assuming EmailService is provided

class RegisterController extends Controller
{
    // Existing methods...

    public function register(RegisterRequest $request)
    {
        // Check if email or username exists
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['message' => 'Email is already registered.'], 409);
        }

        if (User::where('username', $request->username)->exists()) {
            return response()->json(['message' => 'Username is already taken.'], 409);
        }

        // Validate password complexity
        $passwordValidation = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'],
        ]);

        if ($passwordValidation->fails()) {
            return response()->json(['message' => 'Password does not meet the complexity requirements.'], 422);
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
            'username' => $request->username,
        ]);

        // Generate an email verification token and associate it with the user
        $verificationToken = EmailVerificationToken::create([
            'token' => Str::random(60),
            'user_id' => $user->id,
            'created_at' => now(),
            'expires_at' => now()->addHours(24),
        ]);

        // Utilize the `EmailService` to send a confirmation email to the user with the verification token link.
        EmailService::sendVerificationEmail($user->email, $verificationToken->token);

        // Return a response with the user ID
        return response()->json([
            'status' => 201,
            'message' => 'User registered successfully. Please check your email to verify your account.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->toIso8601String(),
            ]
        ], 201);
    }

    // Other methods...
}
