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

            if (!$user) {
                // Always return the same response regardless of user existence to prevent email enumeration
                return response()->json(['message' => 'If your email address exists in our database, you will receive a password reset link.'], 200);
            }

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

            // Always return the same response regardless of user existence to prevent email enumeration
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

    // ... (other methods remain unchanged)

    public function requestResetPassword(Request $request)
    {
        // This method is similar to sendResetLinkEmail but with a different response strategy.
        // To resolve the conflict, we will keep the logic but align the response with sendResetLinkEmail.

        // Validate the email field
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ], [
            'email.required' => 'Email address is required.',
            'email.email' => 'Invalid email format.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first('email')], 400);
        }

        try {
            // Check if the email exists in the database
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                // Always return the same response regardless of user existence to prevent email enumeration
                return response()->json(['message' => 'If your email address exists in our database, you will receive a password reset link.'], 200);
            }

            // Generate a unique token
            $token = Str::random(60);

            // Create a new PasswordResetRequest
            $passwordResetRequest = PasswordResetRequest::create([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => Carbon::now()->addMinutes(config('auth.passwords.users.expire')),
                'status' => 'pending',
            ]);

            // Send an email with the password reset token
            Mail::to($user->email)->send(new PasswordResetMail($token));

            // Always return the same response regardless of user existence to prevent email enumeration
            return response()->json(['message' => 'If your email address exists in our database, you will receive a password reset link.'], 200);
        } catch (Exception $e) {
            // Handle any exceptions
            return response()->json(['message' => 'Failed to send password reset email.'], 500);
        }
    }

    // The initiatePasswordReset method is no longer needed as requestResetPassword method covers the requirement.
    // Therefore, it has been removed to avoid redundancy and potential confusion.

    // New method to handle password reset errors
    public function handlePasswordResetError(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'error_code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Error code is required.'], 422);
        }

        $errorCode = $request->input('error_code');
        $message = 'Error has been handled.';
        $status = 200;

        switch ($errorCode) {
            case 'code_not_found':
                $message = 'Unknown error code.';
                $status = 400;
                break;
            // Add more cases for different error codes as needed
            default:
                $message = 'Unknown error code.';
                $status = 400;
                break;
        }

        return response()->json(['status' => $status, 'message' => $message], $status);
    }
}
