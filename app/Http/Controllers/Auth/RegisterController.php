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
use App\Mail\VerifyEmail;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(Request $request)
    {
        // Custom validation messages
        $messages = [
            'name.required' => 'Name cannot be blank.',
            'email.required' => 'Please enter a valid email address.',
            'email.email' => 'Please enter a valid email address.',
            'username.unique' => 'The username is already in use.',
            'password.min' => 'Password must be at least 8 characters long.',
        ];

        // Validate the request data with custom messages
        $validatedData = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'username' => 'required|string|max:255|unique:users,username',
        ], $messages)->validate();

        // Create the user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'remember_token' => Str::random(60),
            'username' => $validatedData['username'],
        ]);

        // Generate email verification token and send verification email
        $verificationToken = EmailVerificationToken::create([
            'token' => Str::random(40),
            'expires_at' => now()->addHours(24),
            'used' => false,
            'user_id' => $user->id,
        ]);

        // Define the mailable class for the verification email
        $verificationEmail = new VerifyEmail($verificationToken->token);

        // Send the verification email using Laravel's Mail facade
        Mail::to($user->email)->send($verificationEmail);

        // Return a response with the user ID and confirmation message
        return response()->json([
            'status' => 201,
            'message' => 'Registration successful. Please verify your email.'
        ], 201);
    }

    // Other methods...
}
