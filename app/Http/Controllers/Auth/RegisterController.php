<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Http\Resources\UserResource;
use App\Mail\VerifyEmail;
use App\Mail\PasswordResetMail;
use App\Mail\RegistrationConfirmationMail; // Assuming this Mailable class exists
use Illuminate\Support\Facades\Log;

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
        $user->name = $validatedData['display_name'] ?? $validatedData['name']; // Support both 'name' and 'display_name'
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']); // Encrypt the password
        $user->is_stylist = false; // Set the 'is_stylist' attribute to false
        $user->remember_token = Str::random(60); // Generate a verification token

        // Save the new user instance to the database
        $user->save();

        // Send a verification email
        Mail::to($user->email)->send(new VerifyEmail($user->remember_token));

        // Generate a unique token for password reset
        $token = Str::random(60);
        $passwordResetRequest = new PasswordResetRequest([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addDay(),
            'status' => 'pending'
        ]);
        $passwordResetRequest->save();

        // Send an email to the user with the password reset link
        Mail::to($user->email)->send(new PasswordResetMail($token));

        // Return a UserResource instance as the response
        return (new UserResource($user))->additional(['message' => 'Registration successful, verification email sent.']);
    }

    public function sendRegistrationConfirmationEmail($userId, $token)
    {
        try {
            $user = User::findOrFail($userId);
            $emailContent = new RegistrationConfirmationMail($token); // Assuming this Mailable class exists

            Mail::to($user->email)->send($emailContent);

            Log::info('Registration confirmation email sent to user: ' . $user->email);

            return response()->json(['email_sent_status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to send registration confirmation email: ' . $e->getMessage());

            return response()->json(['email_sent_status' => 'failed'], 500);
        }
    }

    // Add any other existing methods here
}
