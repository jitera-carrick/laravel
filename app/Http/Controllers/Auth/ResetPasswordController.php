<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\PasswordReset;
use App\Models\PasswordResetToken; // Import the PasswordResetToken model
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\ResetPasswordConfirmationMail;
use App\Services\PasswordPolicyService; // Assuming this service exists as per the guideline
use App\Exceptions\InvalidEmailException; // Import the InvalidEmailException
use Illuminate\Http\JsonResponse; // Import JsonResponse
use Illuminate\Support\Facades\Log; // Import Log facade for logging exceptions

class ResetPasswordController extends Controller
{
    // Add your existing methods here

    // Method to handle password reset
    public function reset(Request $request)
    {
        // Validate the request input
        $validatedData = $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|confirmed|min:6|regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
            'password_confirmation' => 'required',
        ]);

        // Check that the password is not the same as the email
        if ($validatedData['password'] === $validatedData['email']) {
            return response()->json(['message' => 'Password cannot be the same as the email address.'], 400);
        }

        // Use the `validateResetToken` method to validate the token
        $tokenValidationResponse = $this->validateResetToken($request, $validatedData['token']); // Pass the token directly to the method
        $tokenValidationData = json_decode($tokenValidationResponse->getContent(), true);

        // If the token is invalid or expired, return an appropriate error response
        if (!$tokenValidationData['valid']) {
            return response()->json(['message' => $tokenValidationData['message']], $tokenValidationResponse->status());
        }

        // Retrieve the user by email
        $user = User::where('email', $validatedData['email'])->first();

        // Retrieve password policy
        $passwordPolicyService = new PasswordPolicyService();
        $passwordPolicy = $passwordPolicyService->getPasswordPolicy();

        // Implement the password validation logic according to the password policy
        $passwordValidationErrors = $passwordPolicyService->validatePassword($validatedData['password'], $passwordPolicy);
        if (!empty($passwordValidationErrors)) {
            return response()->json(['errors' => $passwordValidationErrors], 422);
        }

        // Encrypt the new password using `Hash::make` and update the user's password in the "users" table
        $user->password = Hash::make($validatedData['password']);
        $user->save();

        // Update the status of the reset token in the "password_reset_tokens" table to indicate it has been used
        // Check if the PasswordResetToken model exists, if not, use the PasswordReset model
        $passwordResetModel = class_exists(PasswordResetToken::class) ? new PasswordResetToken() : new PasswordReset();
        $passwordReset = $passwordResetModel->where('token', $validatedData['token'])->first();
        $passwordReset->status = 'used';
        $passwordReset->save();

        // Send a confirmation email to the user using the `Mail::to` method and the `ResetPasswordConfirmationMail` Mailable class
        Mail::to($user->email)->send(new ResetPasswordConfirmationMail($user));

        // Trigger a ResetPasswordNotification
        Notification::send($user, new ResetPasswordNotification()); // Use Notification facade as in the existing code

        // Return a success response with a confirmation message indicating that the password has been successfully reset
        return response()->json(['message' => 'Your password has been successfully reset.'], 200);
    }

    // Method to validate reset token
    public function validateResetToken(Request $request, $token = null)
    {
        // Use the token from the request if not provided as a parameter
        $token = $token ?? $request->input('token');

        if (empty($token)) {
            return response()->json(['valid' => false, 'message' => 'Reset token is required.'], 400);
        }

        try {
            // Check if the PasswordResetToken model exists, if not, use the PasswordReset model
            $passwordResetModel = class_exists(PasswordResetToken::class) ? new PasswordResetToken() : new PasswordReset();
            $passwordReset = $passwordResetModel->where('token', $token)->first();

            // Check if the token exists and is not expired
            if (!$passwordReset || $passwordReset->isExpired()) {
                return response()->json(['valid' => false, 'message' => 'The token does not exist or has expired.'], 404);
            }

            // If the token is valid and not expired, return a positive response
            return response()->json(['valid' => true, 'message' => 'The reset token is valid.'], 200);
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error validating reset token: ' . $e->getMessage());

            // Return a generic error message
            return response()->json(['valid' => false, 'message' => 'An error occurred while validating the token.'], 500);
        }
    }

    // Existing methods...
}
