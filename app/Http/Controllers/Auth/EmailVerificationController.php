<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken; // Import the EmailVerificationToken model
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View; // Import the View facade
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    // Existing verify method remains unchanged
    public function verify(Request $request, $token)
    {
        // Find the user by the verification token
        $user = User::where('remember_token', $token)->first();

        // If the token is invalid or expired
        if (!$user) {
            // Check if the request expects JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Invalid or expired token.'], 404);
            } else {
                // Return a view with an error message
                return view('email_verification_error', ['message' => 'Invalid or expired token.']);
            }
        }

        // Update the user's email verification status
        $user->email_verified_at = Carbon::now();
        $user->remember_token = null; // Clear the verification token
        $user->save();

        // Check if the request expects JSON
        if ($request->expectsJson()) {
            // Return a success response
            return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], 200);
        } else {
            // Redirect to a specific route or return a view with a success message
            return redirect('/home')->with('status', 'Email verified successfully.');
        }
    }

    // New verifyEmail method
    public function verifyEmail(Request $request, $token)
    {
        // Ensure the request method is POST
        if (!$request->isMethod('post')) {
            return response()->json(['message' => 'Bad request.'], 400);
        }

        $verificationToken = EmailVerificationToken::where('token', $token)->first();

        if (!$verificationToken) {
            return response()->json(['message' => 'Invalid verification token.'], 404);
        }

        if ($verificationToken->expires_at < now()) {
            return response()->json(['message' => 'The verification token is expired.'], 422);
        }

        if ($verificationToken->used) {
            return response()->json(['message' => 'The verification token has already been used.'], 422);
        }

        $user = $verificationToken->user;
        $user->email_verified_at = now();
        $user->save();

        $verificationToken->used = true;
        $verificationToken->save();

        return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], 200);
    }
}
