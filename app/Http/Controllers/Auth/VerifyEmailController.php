<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyEmailRequest; // Assuming VerifyEmailRequest exists and is properly set up
use App\Models\User;
use App\Models\EmailVerificationToken; // Assuming EmailVerificationToken model exists and has necessary methods
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationComplete; // Assuming RegistrationComplete Mailable exists

class VerifyEmailController extends Controller
{
    public function verify(VerifyEmailRequest $request) // Using VerifyEmailRequest for validation
    {
        $token = $request->input('token');

        // Validation logic for the token (ensure it's a string of 32 characters or more)
        if (strlen($token) < 32) {
            return response()->json(['message' => 'Invalid or expired verification token.'], 400);
        }

        // Find the user by the verification token
        $user = User::where('remember_token', $token)->first();

        // If the token is invalid or expired
        if (!$user) {
            return response()->json(['message' => 'Invalid or expired verification token.'], 404);
        }

        // Assuming EmailVerificationToken has a method `isValidToken` that checks if the token is valid and not expired
        if (!EmailVerificationToken::isValidToken($token, $user->id, $user->email)) {
            return response()->json(['message' => 'Invalid or expired verification token.'], 410);
        }

        // Update the user's email verification status
        $user->is_verified = true; // Set the user's `is_verified` attribute to `true`
        $user->email_verified_at = Carbon::now();
        $user->remember_token = null; // Clear the verification token
        $user->save();

        // Send confirmation email
        Mail::to($user->email)->send(new RegistrationComplete($user));

        // Return a success response
        return response()->json(['status' => 200, 'message' => 'Email address has been successfully verified.'], 200);
    }
}
