
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailVerificationToken;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\VerifyEmailNotification;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'username' => 'required|string|max:255|unique:users,username',
        ]);

        // Check for the uniqueness of the email and username
        if (User::where('email', $validatedData['email'])->orWhere('username', $validatedData['username'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['The provided email is already in use.'],
                'username' => ['The provided username is already in use.'],
            ]);
        }

        // Create the user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'remember_token' => Str::random(60),
            'username' => $validatedData['username'], // Assuming username is part of the request
        ]);

        // Generate email verification token and send verification email
        $verificationToken = EmailVerificationToken::create([
            'token' => Str::random(40),
            'expires_at' => now()->addHours(24),
            'used' => false,
            'user_id' => $user->id,
        ]);

        // Define the mailable class for the verification email
        $verificationEmail = new \App\Mail\VerifyEmail($verificationToken->token);

        // Send the verification email using Laravel's Mail facade
        Mail::to($user->email)->send($verificationEmail);

        // Return a response with the user ID and confirmation message
        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully. Please check your email to verify your account.',
            'verification_link' => route('verification.verify', ['token' => $verificationToken->token]), // Assuming there's a named route for email verification
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
