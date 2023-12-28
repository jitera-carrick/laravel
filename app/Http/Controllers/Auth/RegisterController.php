<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Exception;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Custom error messages
        $messages = [
            'name.required' => 'The name is required.',
            'email.required' => 'The email is required.',
            'email.email' => 'Invalid email format.',
            'email.unique' => 'Email already registered.',
            'password.required' => 'The password is required.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];

        // Validate the request with custom messages
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ], $messages);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Get validated data
        $validatedData = $validator->validated();

        // Start transaction
        DB::beginTransaction();
        try {
            // Create a new user instance
            $user = new User();
            $user->name = $validatedData['name'];
            $user->email = $validatedData['email'];
            $user->password = Hash::make($validatedData['password']);
            $user->email_verified_at = null; // Set email verification flag to null
            $user->save();

            // Generate a verification token
            $token = Str::random(60);
            $passwordResetToken = new PasswordResetToken([
                'email' => $user->email,
                'token' => $token,
                'expires_at' => now()->addHours(24),
                'used' => false,
                'user_id' => $user->id
            ]);
            $passwordResetToken->save();

            // Send a confirmation email
            Mail::to($user->email)->send(new VerificationEmail($token));

            // Commit transaction
            DB::commit();

            // Return a response with the user's details
            return response()->json([
                'status' => 201,
                'message' => 'User registered successfully. A confirmation email has been sent.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'created_at' => $user->created_at->toIso8601String(),
                ]
            ], 201);
        } catch (Exception $e) {
            // Rollback transaction
            DB::rollBack();

            // Return error response
            return response()->json([
                'status' => 500,
                'message' => 'An error occurred during registration.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
