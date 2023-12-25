<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Resources\UserResource;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    // Add any existing methods here

    public function register(RegisterRequest $request)
    {
        // Validate the input data
        $validatedData = $request->validated();

        // Check if the email is already registered
        $existingUser = User::where('email', $validatedData['email'])->first();
        if ($existingUser) {
            return response()->json(['message' => 'The email has already been taken.'], 422);
        }

        // Create a new User instance and fill it with the validated data
        $user = new User();
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']); // Encrypt the password
        $user->is_stylist = false; // Set the 'is_stylist' attribute to false
        $user->remember_token = Str::random(60); // Generate a verification token

        // Save the new user instance to the database
        $user->save();

        // Send a verification email
        Mail::to($user->email)->send(new VerifyEmail($user->remember_token));

        // Return a UserResource instance as the response
        return (new UserResource($user))->additional(['message' => 'Registration successful, verification email sent.']);
    }

    // Add any other existing methods here
}
