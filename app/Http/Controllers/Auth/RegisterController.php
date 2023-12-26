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
use Illuminate\Http\Request;
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
            // Add any other validation rules from the new code here if needed
        ], [
            'email.unique' => 'Invalid email format or email already in use.',
            'password.min' => 'Password does not meet the policy requirements.', // Reverted error message to meet the requirement
            'password.different' => 'Password does not meet the policy requirements.',
            'password.regex' => 'Password does not meet the policy requirements.',
            // Add any other custom validation messages from the new code here if needed
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $validatedData = $validator->validated();

        // Check if the email is already registered
        $existingUser = User::where('email', $validatedData['email'])->first();
        if ($existingUser) {
            return response()->json(['message' => 'The email has already been taken.'], 422);
        }

        // Create a new User instance and fill it with the validated data
        $user = new User();
        // Support both 'name' and 'display_name', with 'name' as a fallback if 'display_name' is not provided
        $user->name = $validatedData['display_name'] ?? $validatedData['name'] ?? $validatedData['email']; // Assuming 'name' is required and using 'email' as a placeholder
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
        return (new UserResource($user))
            ->response()
            ->setStatusCode(201)
            ->additional([
                'message' => 'Registration successful, verification email sent.',
                'email_sent_status' => $emailSentStatus // Include the email sent status in the response
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

    // New method to send registration email as per the guideline
    public function sendRegistrationEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user = User::findOrFail($request->input('user_id'));
        $token = Str::random(60);

        // Save the token to the database (assuming you have a PasswordReset model)
        PasswordResetRequest::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addHours(24),
            'status' => 'pending'
        ]);

        Mail::to($user->email)->send(new PasswordResetMail($token, $user));

        return response()->json([
            'status' => 200,
            'message' => 'Registration confirmation email sent successfully.'
        ]);
    }

    // Add any other existing methods here
}
