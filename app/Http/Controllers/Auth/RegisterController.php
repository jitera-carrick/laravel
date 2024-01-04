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
        // The validation logic has been moved to RegisterRequest, so we no longer need the Validator facade here.
        // We can assume that the request has already been validated by the time it reaches this method.

        // Additional validation for username
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255|unique:users,username',
        ], [
            'username.required' => 'Username cannot be blank.',
            'username.unique' => 'Username already in use.',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username, // Add username to the user creation
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
                'updated_at' => $user->updated_at->toIso8601String(), // Include updated_at in the response
            ]
        ], 201);
    }

    // Other methods...
}
