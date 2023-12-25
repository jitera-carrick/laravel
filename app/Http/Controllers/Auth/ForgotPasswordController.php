<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Models\StylistRequest;
use App\Models\Image;
use Exception;
use Carbon\Carbon;
use App\Mail\PasswordResetMail; // Use the correct Mailable class as per new code
use App\Mail\PasswordResetSuccessMail; // Assuming this Mailable class exists
use App\Mail\PasswordResetConfirmationMail; // Assuming this Mailable exists
use App\Mail\PasswordSetConfirmationMail; // Assuming this Mailable exists for password set confirmation

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // Validate the email field
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first('email'), 'reset_requested' => false], 422);
        }

        try {
            // Find the user by email
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['message' => 'User does not exist.', 'reset_requested' => false], 404);
            }

            // Generate a unique token
            $token = Str::random(60);

            // Create a new password reset request
            $passwordResetRequest = new PasswordResetRequest([
                'user_id' => $user->id,
                'token' => $token,
                'expires_at' => now()->addMinutes(config('auth.passwords.users.expire')),
                'status' => 'pending', // Use 'pending' status as per new code
            ]);
            $passwordResetRequest->save();

            // Send the password reset email
            Mail::to($user->email)->send(new PasswordResetMail($token)); // Use the correct Mailable class 'PasswordResetMail' as per new code

            return response()->json(['message' => 'Password reset email sent.', 'reset_requested' => true], 200);
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
}
