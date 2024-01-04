
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Models\EmailVerificationToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\VerifyEmailNotification;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(RegisterRequest $request)
    {
        // Since we're using a form request, we can assume the data is already validated.
        $validatedData = $request->validated();

        // Create the user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'remember_token' => Str::random(60),
            'username' => $validatedData['username'], // Assuming username is part of the request
        ]);

        // Generate email verification token
        $verificationToken = EmailVerificationToken::create([
            'token' => Str::random(40),
            'expires_at' => now()->addHours(24),
            'used' => false,
            'user_id' => $user->id,
        ]);

        // Send verification email
        $user->notify(new VerifyEmailNotification($verificationToken->token));

        // Return a response with the user ID and confirmation message
        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully. Please check your email to verify your account.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->toIso8601String(),
            ]
        ]);
    }

    // Other methods...
}
