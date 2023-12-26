<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\VerifyEmail;
use Carbon\Carbon; // Import Carbon for timestamp handling

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Custom error messages for validation
        $messages = [
            'name.required' => 'The name is required.',
            'email.required' => 'Invalid email format.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'Email already registered.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'Passwords do not match.',
        ];

        // Updated validation with custom error messages
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'keep_session' => 'sometimes|boolean' // Added from new code
        ], $messages);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $validatedData = $validator->validated();

        // Check if the email already exists in the database using the User model
        $user = User::where('email', $validatedData['email'])->first();

        if ($user) {
            return response()->json(['message' => 'Email already registered.'], 422);
        }

        // Create a new User instance and save it to the database
        $user = new User;
        $user->name = $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']);
        $user->email_verified_at = null;
        $user->created_at = Carbon::now(); // Set the current datetime for "created_at"
        $user->updated_at = Carbon::now(); // Set the current datetime for "updated_at"
        $user->keep_session = $request->input('keep_session', false); // Added from new code
        $user->save();

        if ($user->keep_session) { // Added from new code
            Auth::login($user);
        }

        // Generate a verification token using Str::random(60)
        $verificationToken = Str::random(60);
        $user->remember_token = $verificationToken; // Save the verification token in the remember_token column
        $user->save(); // Save the user again after adding the remember_token

        // Send an email verification using the Mail facade
        Mail::send('emails.verify_email', ['token' => $verificationToken], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Email Verification');
        });

        // Return a 201 Created response with a success message upon successful registration
        return response()->json([
            'status' => 201,
            'message' => 'User registered successfully. Please check your email to verify your account.',
            'user' => [ // Added from new code
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->toIso8601String(),
            ]
        ], 201);
    }

    // ... rest of the RegisterController code ...
}
