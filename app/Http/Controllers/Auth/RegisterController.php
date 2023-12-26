<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\PasswordResetRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Http\Resources\UserResource;
use App\Mail\VerifyEmail;
use App\Mail\PasswordResetMail;
use App\Mail\RegistrationConfirmationMail; // Ensure this class exists in the specified namespace
use Illuminate\Support\Carbon;

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
        // Support both 'name' and 'display_name', with 'name' as a fallback if 'display_name' is not provided
        $user->name = $validatedData['display_name'] ?? $validatedData['name'];
        $user->email = $validatedData['email'];
        $user->password = Hash::make($validatedData['password']); // Encrypt the password
        $user->is_stylist = false; // Set the 'is_stylist' attribute to false
        $user->remember_token = Str::random(60); // Generate a verification token

        // Save the new user instance to the database
        $user->save();

        // Generate a unique token for password reset and save it
        $token = Str::random(60);
        $passwordResetRequest = new PasswordResetRequest([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addHours(24), // Use Carbon for consistency
            'status' => 'pending'
        ]);
        $passwordResetRequest->save();

        // Send a verification email
        Mail::to($user->email)->send(new VerifyEmail($user->remember_token));

        // Send registration confirmation email
        $emailSentStatus = $this->sendRegistrationConfirmationEmail($user->id, $token);

        // Return a UserResource instance as the response
        return (new UserResource($user))->additional([
            'message' => 'Registration successful, verification email sent.',
            'email_sent_status' => $emailSentStatus
        ]);
    }

    public function sendRegistrationConfirmationEmail($userId, $token)
    {
        try {
            $user = User::findOrFail($userId);
            $verificationUrl = url('/password/set/' . $token); // Replace with the actual URL for setting the password
            $emailContent = new RegistrationConfirmationMail($verificationUrl); // Pass the URL to the Mailable

            Mail::to($user->email)->send($emailContent);

            Log::info('Registration confirmation email sent to user: ' . $user->email);

            return true; // Email sent successfully
        } catch (\Exception $e) {
            Log::error('Failed to send registration confirmation email: ' . $e->getMessage());

            return false; // Email sending failed
        }
    }

    // Add any other existing methods here
}
