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
use Illuminate\Support\Facades\Notification;
use Illuminate\Http\JsonResponse;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(RegisterRequest $request): JsonResponse
    {
        // Validate that all required fields are provided and not empty.
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:6',
                'different:email',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/'
            ],
            'display_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'required|date',
        ], [
            'email.required' => 'Invalid email address or already in use.',
            'email.email' => 'Invalid email address or already in use.',
            'email.max' => 'Invalid email address or already in use.',
            'email.unique' => 'Invalid email address or already in use.',
            'password.required' => 'Invalid password format.',
            'password.min' => 'Invalid password format.',
            'password.different' => 'Invalid password format.',
            'password.regex' => 'Invalid password format.',
            'display_name.required' => 'The display name is required.',
            'gender.required' => 'The gender field is required.',
            'gender.in' => 'Invalid gender value.',
            'date_of_birth.required' => 'The date of birth is required.',
            'date_of_birth.date' => 'Invalid date format.',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            if ($errors->has('email')) {
                return response()->json(['errors' => $errors], 409);
            }
            return response()->json(['errors' => $errors], 422);
        }

        // Encrypt the password using Laravel's Hash facade
        $hashedPassword = Hash::make($request->password);

        // Create the user with the validated data
        $user = User::create([
            'email' => $request->email,
            'password' => $hashedPassword,
            'display_name' => $request->display_name,
            'gender' => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'is_verified' => false,
            // 'created_at' and 'updated_at' will be automatically set by Eloquent
        ]);

        // Generate a verification token
        $verificationToken = Str::random(32);

        // Send a verification email to the user
        Notification::send($user, new VerifyEmailNotification($verificationToken));

        // Return a JSON response with the user ID and verification status
        return response()->json([
            'status' => 201,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'display_name' => $user->display_name,
                'gender' => $user->gender,
                'date_of_birth' => $user->date_of_birth->format('Y-m-d'),
                'is_verified' => $user->is_verified,
                'created_at' => $user->created_at->toIso8601String(),
            ]
        ], 201);
    }

    // Other methods...
}
