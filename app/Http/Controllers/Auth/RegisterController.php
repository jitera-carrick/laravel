<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Requests\RegisterUserRequest; // Updated import statement
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Notifications\VerifyEmailNotification;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(RegisterUserRequest $request)
    {
        // The new requirement specifies that we need to validate "username" as well.
        // We need to update the validation rules to include "username".
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'username' => 'required|string|max:255|unique:users,username', // New validation rule for username
            'password' => [
                'required',
                'string',
                'min:8', // Assuming this is the complexity requirement
                // Add any other password complexity validations here
            ],
        ], [
            'name.required' => 'The name is required.',
            'email.required' => 'Invalid or already used email address.',
            'email.email' => 'Invalid or already used email address.',
            'email.max' => 'Invalid or already used email address.',
            'email.unique' => 'Invalid or already used email address.',
            'username.required' => 'Invalid or already used username.',
            'username.max' => 'Invalid or already used username.',
            'username.unique' => 'Invalid or already used username.',
            'password.required' => 'Password does not meet the complexity requirements.',
            'password.min' => 'Password does not meet the complexity requirements.',
            // Add any other password complexity validation messages here
        ]);

        if ($validator->fails()) {
            // We need to determine the correct status code based on the error.
            // If the error is due to unique constraint, we return 409 Conflict.
            $statusCode = 400;
            foreach ($validator->errors()->getMessages() as $field => $messages) {
                if (in_array($field, ['email', 'username']) && Str::contains($messages[0], 'already used')) {
                    $statusCode = 409;
                    break;
                }
            }
            return response()->json([
                'status' => $statusCode,
                'message' => $validator->errors()->first()
            ], $statusCode);
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username, // Save the username
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
            // 'created_at' and 'updated_at' will be automatically set by Eloquent
        ]);

        // Send verification email
        $user->notify(new VerifyEmailNotification($user->remember_token));

        // Return a response with the user ID
        return response()->json([
            'status' => 201,
            'message' => 'User registered successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username, // Include username in the response
                'created_at' => $user->created_at->toIso8601String(),
            ]
        ], 201);
    }

    // Other methods...
}
