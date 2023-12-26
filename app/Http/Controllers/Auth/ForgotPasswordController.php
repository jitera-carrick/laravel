<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Str;
use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Models\StylistRequest;
use App\Models\Image;
use Exception;
use Carbon\Carbon;
use App\Mail\PasswordResetMail;
use App\Mail\PasswordResetSuccessMail;
use App\Mail\PasswordResetConfirmationMail;
use App\Mail\PasswordSetConfirmationMail;

class ForgotPasswordController extends Controller
{
    public function __construct()
    {
        // Constructor can be empty if no services are injected
    }

    public function sendResetLinkEmail(Request $request)
    {
        // The logic from the new code's sendResetLinkEmail method is merged here
        // Validate the email field
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            // Merged the error message to be more generic and include both cases
            return response()->json(['message' => 'Invalid email format or email is required.', 'reset_requested' => false], 422);
        }

        try {
            // Find the user by email
            $user = User::where('email', $request->email)->first();

            if ($user) {
                // Generate a unique token
                $token = Str::random(60);

                // Create a new password reset request
                $passwordResetRequest = PasswordResetRequest::create([
                    'user_id' => $user->id,
                    'token' => $token,
                    'expires_at' => now()->addMinutes(config('auth.passwords.users.expire')),
                    'status' => 'pending',
                ]);

                // Send the password reset email
                Mail::to($user->email)->send(new PasswordResetMail($token));

                // Update the status of the password reset request to "sent"
                $passwordResetRequest->update(['status' => 'sent']);
            }

            // Always return a successful response to prevent email enumeration
            return response()->json(['message' => 'If your email address exists in our database, you will receive a password reset link.'], 200);
        } catch (Exception $e) {
            // Handle any exceptions that occur during the process
            return response()->json(['message' => 'Failed to send password reset email.', 'reset_requested' => false], 500);
        }
    }

    public function validateResetToken(Request $request)
    {
        // ... (existing validateResetToken method code remains unchanged)
    }

    public function verifyEmail(Request $request)
    {
        // ... (existing verifyEmail method code remains unchanged)
    }

    public function verifyEmailAndSetPassword(Request $request)
    {
        // ... (existing verifyEmailAndSetPassword method code remains unchanged)
    }

    // The handlePasswordResetError method from the new code is merged here.
    public function handlePasswordResetError(Request $request)
    {
        // Validate the email field
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'error_code' => 'sometimes|required|string',
        ]);

        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->errors()->getMessages() as $field => $message) {
                $errors[] = [
                    'field' => $field,
                    'message' => $message[0]
                ];
            }
            // Return a 422 Unprocessable Entity with the validation errors
            return response()->json([
                'status' => 422,
                'errors' => $errors
            ], 422);
        }

        try {
            // Business logic for handling password reset errors
            // ...

            // If everything is fine, you can return a success response or whatever is needed
            return response()->json([
                'status' => 200,
                'message' => 'Password reset error handled successfully.'
            ]);
        } catch (Exception $e) {
            // Handle any exceptions that occur during the process
            return response()->json([
                'status' => 500,
                'errors' => ['message' => 'An unexpected error occurred.']
            ], 500);
        }
    }

    // ... (other methods remain unchanged)
}
