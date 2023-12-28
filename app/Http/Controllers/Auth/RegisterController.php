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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|regex:/^.+@.+$/i',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Check if the email already exists
        $existingUser = User::where('email', $request->email)->first();
        if ($existingUser) {
            throw ValidationException::withMessages([
                'email' => ['The email has already been taken.'],
            ]);
        }

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Send verification email
        $user->notify(new VerifyEmailNotification($user->remember_token));

        // Return a response with the user ID
        return response()->json(['user_id' => $user->id], 201);
    }

    // Other methods...
}
