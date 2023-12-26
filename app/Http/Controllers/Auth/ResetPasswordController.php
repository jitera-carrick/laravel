<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PasswordResetRequest;
use Exception;

class ResetPasswordController extends Controller
{
    /**
     * Validate the password reset token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateResetToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|exists:password_reset_requests,token',
        ]);

        if ($validator->fails()) {
            return response()->json(['valid' => false, 'message' => $validator->errors()->all()], 422);
        }

        $token = $request->token;
        $passwordResetRequest = PasswordResetRequest::where('token', $token)
                            ->where('expires_at', '>', now())
                            ->first();

        if (!$passwordResetRequest) {
            return response()->json([
                'valid' => false,
                'message' => 'The password reset token is invalid or has expired.'
            ], 404);
        }

        return response()->json([
            'valid' => true,
            'message' => 'The password reset token is valid.'
        ]);
    }

    public function reset(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            // Merged password validation rules to include complexity requirements from existing code
            'password' => 'required|confirmed|min:8|regex:/[a-z]/|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/', 
            'token' => 'required|string',
        ], [
            // Custom error messages for password validation from existing code
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must include at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*#?&).',
        ]);

        if ($validator->fails()) {
            // Return a generic message to avoid giving specific details about the failure from existing code
            return response()->json(['message' => 'There was an error with your request. Please ensure all fields are filled out correctly.'], 422);
        }

        try {
            // Check if the token is valid
            $passwordResetRequest = PasswordResetRequest::where('token', $request->token)
                ->where('expires_at', '>', now())
                ->first();

            if (!$passwordResetRequest) {
                // Return a generic message to avoid revealing token status from existing code
                return response()->json(['message' => 'A password reset link has been sent to the provided email if it exists in our system.'], 200);
            }

            // Update the user's password
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // Invalidate the token
            $passwordResetRequest->delete();

            // Always display a message indicating that a password reset email has been sent from existing code
            return response()->json(['message' => 'Your password has been reset successfully. A confirmation email has been sent.'], 200);
        } catch (Exception $e) {
            // Return a generic error message to avoid revealing sensitive information from existing code
            return response()->json(['message' => 'An error occurred while processing your request. Please try again later.'], 500);
        }
    }
}
