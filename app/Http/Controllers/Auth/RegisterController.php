<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailVerificationToken;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(Request $request)
    {
        // Perform validation
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:8',
        ], [
            'name.required' => 'Name is required.',
            'email.required' => 'Invalid email address format or email already exists.',
            'username.required' => 'Username is required or already exists.',
            'password.required' => 'Password is required and must be strong.',
        ]);

        try {
            // Create the user with the validated data
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'username' => $validatedData['username'], // Added username to the user creation
                'password' => Hash::make($validatedData['password']),
                'remember_token' => Str::random(60),
            ]);

            // Generate email verification token
            $token = Str::random(60);
            EmailVerificationToken::create([
                'token' => $token,
                'user_id' => $user->id,
                'expires_at' => now()->addHours(24),
                'used' => false
            ]);

            // Send verification email
            $verificationUrl = route('verification.verify', ['token' => $token]);
            Mail::send('emails.verify', ['url' => $verificationUrl], function ($message) use ($user) {
                $message->to($user->email)->subject('Verify Email Address');
            });

            // Return a response with the user ID
            return response()->json([
                'status' => 201,
                'message' => 'Registration successful. Please verify your email.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username, // Include username in the response
                    'created_at' => $user->created_at->toIso8601String(),
                ]
            ], 201);
        } catch (\Illuminate\Database\QueryException $e) {
            $errorCode = $e->errorInfo[1];
            if ($errorCode == 1062) {
                // Duplicate entry
                return response()->json([
                    'status' => 409,
                    'message' => 'The email or username already exists.'
                ], 409);
            }
            // Other database related errors
            return response()->json([
                'status' => 500,
                'message' => 'An unexpected error occurred on the server.'
            ], 500);
        }
    }

    // Other methods...
}
