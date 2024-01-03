<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Mail\EmailVerificationMail;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(RegisterRequest $request)
    {
        // Validate that all required fields are provided and not empty.
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'name.required' => 'The name is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Invalid email format.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'email.unique' => 'Email already registered.',
            'password.required' => 'The password field is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Hash the password with a generated salt
        $salt = Str::random(16);
        $hashedPassword = Hash::make($request->password . $salt);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password_hash' => $hashedPassword,
            'password_salt' => $salt,
            'remember_token' => Str::random(60),
            // 'created_at' and 'updated_at' will be automatically set by Eloquent
        ]);

        // Generate an email verification token
        $verificationToken = Str::random(60);
        EmailVerificationToken::create([
            'token' => $verificationToken,
            'expires_at' => now()->addHours(24),
            'used' => false,
            'user_id' => $user->id,
        ]);

        // Send confirmation email
        Mail::to($user->email)->send(new EmailVerificationMail($verificationToken));

        // Return a response with the user ID
        return response()->json([
            'status' => 'success',
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
