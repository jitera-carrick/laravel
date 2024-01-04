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
        // Validate that all required fields are provided and not empty.
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users,name',
            'email' => 'required|string|email|max:255|unique:users,email',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'], // Updated password validation to check for a strong password
        ], [
            'name.required' => 'The name is required.',
            'name.unique' => 'The name is already taken.',
            'email.required' => 'The email field is required.',
            'email.email' => 'Invalid email format.',
            'email.max' => 'The email may not be greater than 255 characters.',
            'email.unique' => 'Email address is already registered.', // Updated error message to match requirement
            'username.required' => 'The username field is required.',
            'username.unique' => 'Username is already taken.', // Error message matches requirement
            'password.required' => 'The password field is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.regex' => 'Password is too weak.', // Added error message for weak password
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // Changed from throwing an exception to returning JSON response
        }

        // Check if the email or username already exists and create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
            // 'created_at' and 'updated_at' will be automatically set by Eloquent
        ]);

        // Generate email verification token
        $verificationToken = EmailVerificationToken::create([
            'token' => Str::random(60),
            'user_id' => $user->id,
            'expires_at' => now()->addHours(24),
            'used' => false,
            // 'created_at' and 'updated_at' will be automatically set by Eloquent
        ]);

        // Send verification email with the verification token link
        $user->notify(new VerifyEmailNotification($verificationToken->token));

        // Return a success response
        return response()->json([
            'status' => 200,
            'message' => 'Registration successful. Please verify your email.',
        ], 200); // Updated the status code and message to match requirement
    }

    // Other methods...
}
