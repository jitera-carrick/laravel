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
use App\Mail\EmailVerificationMail;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(RegisterRequest $request)
    {
        // Update validation rules to include username and more complex password requirements
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[\W_]).{8,}$/'
            ],
            'username' => 'required|string|max:255|unique:users,username',
        ], [
            'name.required' => 'The name is required.',
            'email.required' => 'Invalid or already used email address.',
            'password.required' => 'Password does not meet the complexity requirements.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.regex' => 'Password must include at least one letter, one number, and one special character.',
            'username.required' => 'Username is required.',
            'username.unique' => 'Username is already taken.',
        ]);

        if ($validator->fails()) {
            // Choose the appropriate response code based on the validation error
            $responseCode = $validator->errors()->has('email') || $validator->errors()->has('username') ? 409 : 400;
            throw new ValidationException($validator, response()->json($validator->errors(), $responseCode));
        }

        // Hash the password with a generated salt
        $salt = Str::random(16);
        $hashedPassword = Hash::make($request->password . $salt);

        // Create the user with the new username field
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username, // Add the username field
            'password_hash' => $hashedPassword,
            'password_salt' => $salt,
            'remember_token' => Str::random(60),
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

        // Return a successful response with the user ID
        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully. Please check your email to verify your account.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username, // Include the username in the response
                'created_at' => $user->created_at->toIso8601String(),
            ]
        ], 201);
    }

    // Other methods...
}
