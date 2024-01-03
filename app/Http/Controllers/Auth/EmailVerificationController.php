<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $token)
    {
        // Validate the token length
        if (strlen($token) < 32) {
            return response()->json(['message' => 'Invalid or expired verification token.'], 400);
        }

        try {
            // Use the EmailVerificationToken model to query the database for the token
            $verificationToken = EmailVerificationToken::where('token', $token)
                ->where('expires_at', '>', Carbon::now())
                ->where('used', false)
                ->first();

            // If the token is invalid or expired
            if (!$verificationToken) {
                return response()->json(['message' => 'Invalid or expired verification token.'], 410);
            }

            // Find the associated user and set the `is_verified` attribute to `true`
            $user = User::find($verificationToken->user_id);
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $user->is_verified = true;
            $user->email_verified_at = Carbon::now(); // Set the email_verified_at timestamp
            $user->save();

            // After successful verification, delete the token record from the database
            $verificationToken->delete();

            // Return a JSON response with a 200 status code and a success message
            return response()->json(['status' => 200, 'message' => 'Email address has been successfully verified.'], 200);
        } catch (\Exception $e) {
            // Handle any exceptions and return a 500 status code with an error message
            return response()->json(['message' => 'An unexpected error occurred.'], 500);
        }
    }
}
