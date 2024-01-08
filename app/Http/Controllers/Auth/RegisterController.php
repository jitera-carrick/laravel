<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\VerifyEmailNotification;
use App\Models\EmailVerification;

class RegisterController extends Controller
{
    // Existing methods...
    
    public function register(RegisterRequest $request)
    {
        // Validate that all required fields are provided and not empty.
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).+$/'
            ],
        ], [
            'email.required' => 'Invalid email address.',
            'email.email' => 'Invalid email address.',
            'email.max' => 'Invalid email address.',
            'email.unique' => 'The email is already in use.',
            'password.required' => 'Invalid password. Password must be at least 8 characters long and include a number, a letter, and a special character.',
            'password.min' => 'Invalid password. Password must be at least 8 characters long and include a number, a letter, and a special character.',
            'password.confirmed' => 'The password confirmation does not match.',
            'password.regex' => 'Invalid password. Password must be at least 8 characters long and include a number, a letter, and a special character.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Create the user and set email_verified_at to null
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
            // 'created_at' and 'updated_at' will be automatically set by Eloquent
        ]);
        $user->email_verified_at = null;
        $user->save();

        // Generate a unique email verification token and associate it with the user
        $emailVerification = EmailVerification::create([
            'token' => Str::random(60),
            'user_id' => $user->id,
        ]);

        // Send verification email with the verification token link
        $user->notify(new VerifyEmailNotification($emailVerification->token));

        // Return a response with the user ID
        return response()->json([
            'status' => 201,
            'message' => 'User registered successfully. Please check your email to verify your account.',
        ], 201);
    }

    // Other methods...
}
