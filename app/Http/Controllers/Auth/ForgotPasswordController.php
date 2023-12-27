<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\PasswordResetToken;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response; // Import Response facade

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email field
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'If your email address is in our database, you will receive a password reset link.'], 400);
        }

        // Always return the same response to prevent email enumeration
        $responseMessage = 'If your email address is in our database, you will receive a password reset link.';

        try {
            // Find the user by email
            $user = User::where('email', $request->email)->first();

            if ($user) {
                // Generate a unique reset token and expiration time
                $token = Str::random(60);
                $expiration = Carbon::now()->addMinutes(60);

                // Create a new entry in the password_reset_tokens table
                $passwordResetToken = PasswordResetToken::create([
                    'email' => $user->email,
                    'token' => $token,
                    'created_at' => now(),
                    'expires_at' => $expiration,
                    'user_id' => $user->id,
                ]);

                // Send the password reset email
                Mail::send('emails.password_reset', ['token' => $token], function ($message) use ($user) {
                    $message->to($user->email);
                    $message->subject('Password Reset Link');
                });
            }

            return response()->json(['message' => $responseMessage], 200);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Failed to send password reset link.'], 500);
        }
    }

    // ... Rest of the existing code in ForgotPasswordController

    /**
     * Handle the password reset process.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        // Validate the input fields
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'new_password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        // Find the password reset token for the user
        $tokenRecord = PasswordResetToken::where('email', $request->email)->latest()->first();

        if (!$tokenRecord) {
            return response()->json(['message' => 'Invalid password reset token.'], 400);
        }

        // Check if the token has expired
        if (Carbon::parse($tokenRecord->expires_at)->isPast()) {
            return response()->json(['message' => 'Password reset token has expired.'], 400);
        }

        // Update the user's password
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Invalidate the password reset token
        $tokenRecord->update(['status' => 'used']);

        return response()->json(['message' => 'Password has been successfully reset.'], 200);
    }

    /**
     * Handle a password reset request.
     *
     * This method is from the existing code and is similar to the new code's initiatePasswordReset method.
     * We will keep this method and remove initiatePasswordReset from the new code to avoid duplication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestPasswordReset(Request $request)
    {
        // This method is kept from the existing code and will replace the initiatePasswordReset method from the new code.
        return $this->sendResetLinkEmail($request);
    }

    /**
     * Handle the incoming POST request for password reset errors.
     *
     * This method is from the new code and does not conflict with the existing code.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handlePasswordResetErrors(Request $request)
    {
        // This method is added from the new code and does not conflict with the existing code.
        // Validate the 'email' parameter
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $response = [];
            if ($errors->has('email')) {
                $response['message'] = $errors->first('email') === 'The email field is required.' ? 'Email address is required.' : 'Invalid email address format.';
            }
            return Response::json($response, 422);
        }

        try {
            // Your password reset logic here
            // ...

            return Response::json(['status' => 200, 'message' => 'An error occurred during the password reset process. Please try again.'], 200);
        } catch (Exception $e) {
            return Response::json(['message' => 'An error occurred during the password reset process.'], 500);
        }
    }

    // ... Rest of the existing code in ForgotPasswordController
}
