<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Mail;
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
        // Merge the validation rules and custom error messages from both versions
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&]).+$/',
            'token' => 'required|string',
        ], [
            'password.min' => 'The password must be at least 8 characters.',
            'password.regex' => 'The password must include at least one uppercase letter, one lowercase letter, one number, and one special character (@$!%*#?&).',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'There was an error with your request. Please ensure all fields are filled out correctly.'], 422);
        }

        try {
            // Retrieve the PasswordResetRequest using the provided token and ensure it has not expired
            $passwordResetRequest = PasswordResetRequest::where('token', $request->token)
                ->where('expires_at', '>', now())
                ->where('status', '!=', 'completed')
                ->first();

            if (!$passwordResetRequest) {
                return response()->json(['message' => 'A password reset link has been sent to the provided email if it exists in our system.'], 200);
            }

            // Find the associated user and update their password
            $user = User::where('email', $request->email)->first();
            if (Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'The password cannot be the same as the current password.'], 400);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            // Set the status of the PasswordResetRequest to 'completed' and save the changes
            $passwordResetRequest->status = 'completed';
            $passwordResetRequest->save();

            // Send a confirmation email to the user
            Mail::to($user->email)->send(new PasswordResetMail());

            // Always display a message indicating that a password reset email has been sent
            return response()->json(['message' => 'Your password has been reset successfully. A confirmation email has been sent.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred while processing your request. Please try again later.'], 500);
        }
    }
}
