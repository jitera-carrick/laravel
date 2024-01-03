<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB; // Import the DB facade
use Illuminate\Support\Facades\View; // Import the View facade
use Illuminate\Validation\ValidationException;
use App\Http\Requests\VerifyEmailRequest; // Import the VerifyEmailRequest

class EmailVerificationController extends Controller
{
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

    // New method to handle POST request for email verification
    public function postVerify(VerifyEmailRequest $request)
    {
        $token = $request->input('token');

        DB::beginTransaction();
        try {
            $verificationToken = EmailVerificationToken::where('token', $token)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->first();

            if (!$verificationToken) {
                DB::rollBack();
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Invalid verification token.'], 404);
                } else {
                    return view('email_verification_error', ['message' => 'Invalid verification token.']);
                }
            }

            $user = $verificationToken->user;
            if (!$user) {
                DB::rollBack();
                return response()->json(['message' => 'Invalid verification token.'], 404);
            }

            $user->email_verified_at = now();
            $user->save();

            $verificationToken->used = true;
            $verificationToken->save();

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], 200);
            } else {
                return redirect('/home')->with('status', 'Email verified successfully.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'An unexpected error occurred.'], 500);
            } else {
                return view('email_verification_error', ['message' => 'An unexpected error occurred.']);
            }
        }
    }
}
