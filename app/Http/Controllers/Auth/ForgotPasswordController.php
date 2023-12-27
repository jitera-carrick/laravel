<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\PasswordResetToken;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            // Use the generic error message from the new code to keep it consistent
            return response()->json(['message' => $validator->errors()->first('email')], 400);
        }

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Generate a unique reset token and expiration time
            $token = Str::random(60);
            // Use addMinutes(60) as per new code guideline
            $expiration = Carbon::now()->addMinutes(60);

            // Create a new entry in the password_reset_tokens table
            $passwordResetToken = PasswordResetToken::create([
                'email' => $user->email,
                'token' => $token,
                'created_at' => now(),
                'expires_at' => $expiration,
                'user_id' => $user->id, // Keep the user_id association
                'status' => 'pending' // Keep the status tracking
            ]);

            // Send the password reset email
            try {
                Mail::send('emails.password_reset', ['token' => $token], function ($message) use ($user) {
                    $message->to($user->email);
                    $message->subject('Password Reset Link');
                });

                // Update the status in the password_reset_tokens table to 'sent'
                $passwordResetToken->update(['status' => 'sent']); // Keep the status update

                // Return a success response
                // Use the success message from the existing code as it is more descriptive
                return response()->json(['message' => 'A password reset link has been sent to your email address.'], 200);
            } catch (Exception $e) {
                // Log the exception using Log facade as per existing code
                Log::error($e->getMessage());

                // Return a failure response
                return response()->json(['message' => 'Failed to send password reset link.'], 500);
            }
        }

        // Return a response with a generic message
        // Use the generic message from the new code to keep it consistent
        return response()->json(['message' => 'If your email address is in our database, you will receive a password reset link.'], 200);
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
     * Handle the incoming POST request for password reset errors.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handlePasswordResetErrors(Request $request)
    {
        // Validate the 'email' parameter
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            // Return a JSON response with a 422 status code and the validation errors
            $errors = $validator->errors();
            $response = [];
            if ($errors->has('email')) {
                $response['message'] = $errors->first('email') === 'The email field is required.' ? 'Email address is required.' : 'Invalid email address format.';
            }
            return Response::json($response, 422);
        }

        // Simulate an error during the password reset process
        try {
            // Your password reset logic here
            // ...

            // If the error handling process completes successfully, return a 200 status code with a success message
            return Response::json(['status' => 200, 'message' => 'An error occurred during the password reset process. Please try again.'], 200);
        } catch (Exception $e) {
            // If an error occurs, return a JSON response with a 500 status code and an appropriate error message
            return Response::json(['message' => 'An error occurred during the password reset process.'], 500);
        }
    }

    // ... Rest of the existing code in ForgotPasswordController
}
