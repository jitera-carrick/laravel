<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Resources\UserResource;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    // Add any existing methods here

    public function register(RegisterRequest $request): JsonResponse
    {
        // Custom validation logic
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'min:6', // Updated minimum length requirement back to 6 as per requirement
                'different:email',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/' // Ensuring the password contains both letters and numbers
            ],
        ], [
            'email.unique' => 'Invalid email format or email already in use.',
            'password.min' => 'Password does not meet the policy requirements.', // Reverted error message to meet the requirement
            'password.different' => 'Password does not meet the policy requirements.',
            'password.regex' => 'Password does not meet the policy requirements.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $validatedData = $validator->validated();

        // Create a new User instance and fill it with the validated data
        $user = new User();
        $user->name = $validatedData['email']; // Assuming 'name' is required and using 'email' as a placeholder
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']); // Encrypt the password
        $user->is_stylist = false; // Set the 'is_stylist' attribute to false
        $user->remember_token = Str::random(60); // Generate a verification token

        // Save the new user instance to the database
        $user->save();

        // Send a verification email
        Mail::to($user->email)->send(new VerifyEmail($user->remember_token));

        // Return a UserResource instance as the response
        return (new UserResource($user))
            ->response()
            ->setStatusCode(201)
            ->additional(['message' => 'Registration successful, verification email sent.']);
    }

    // Add any other existing methods here
}
