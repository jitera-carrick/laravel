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

class RegisterController extends Controller
{
    // Existing methods...

    public function register(RegisterRequest $request)
    {
        // Validate that all required fields are provided and not empty.
        // The new code uses Validator::make with $request->validated(), which is unnecessary since RegisterRequest already handles validation.
        // We will use the existing validation method from the old code which is more concise and leverages the FormRequest validation.
        $validatedData = $request->validate([
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

        // Create the user
        // The new code assumes that the password is hashed by a mutator in the User model.
        // To ensure compatibility, we will use the Hash facade as in the existing code.
        // This will work regardless of whether the User model has a mutator for the password attribute.
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'remember_token' => Str::random(60),
            // 'created_at' and 'updated_at' will be automatically set by Eloquent
        ]);

        // Send verification email
        // This part is the same in both the new and existing code.
        $user->notify(new VerifyEmailNotification($user->remember_token));

        // Return a response with the user ID
        // This part is the same in both the new and existing code.
        return response()->json([
            'status' => 201,
            'message' => 'User registered successfully.',
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
