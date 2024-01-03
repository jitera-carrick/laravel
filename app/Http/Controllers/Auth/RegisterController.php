<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Mail\EmailVerificationMail;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    // Existing methods...

    public function register(RegisterRequest $request)
    {
        // Start transaction
        DB::beginTransaction();

        try {
            // Retrieve the validated data from the RegisterRequest
            $validatedData = $request->validated();

            // Hash the password with a generated salt
            $salt = Str::random(16);
            $hashedPassword = Hash::make($validatedData['password'] . $salt);

            // Store the new user's information in the "users" table, including the hashed password and salt
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password_hash' => $hashedPassword,
                'password_salt' => $salt,
                'remember_token' => Str::random(60),
            ]);

            // Generate an email verification token and store it in the "email_verification_tokens" table with the user's ID and set an expiration time for the token
            $verificationToken = Str::random(60);
            EmailVerificationToken::create([
                'token' => $verificationToken,
                'expires_at' => now()->addHours(24),
                'used' => false,
                'user_id' => $user->id,
            ]);

            // Send a confirmation email to the user with the verification token link
            Mail::to($user->email)->send(new EmailVerificationMail($verificationToken));

            // Commit the transaction
            DB::commit();

            // Return a success response indicating the user has been registered and a confirmation email has been sent
            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully. Please check your email to verify your account.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->toIso8601String(),
                ]
            ], 201);
        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Return a JSON response with the error message
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Other methods...
}
