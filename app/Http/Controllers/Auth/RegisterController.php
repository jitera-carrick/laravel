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

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        // Validate the request
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Check if the email is unique
        if (User::where('email', $request->email)->exists()) {
            return response()->json(['message' => 'The email has already been taken.'], 400);
        }

        // Create a new user instance
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
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

        // Return a response with the user's details
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'message' => 'Registration successful. Please check your email to verify your account.'
        ], 201);
    }
}
