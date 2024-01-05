<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class VerifyEmailController extends Controller
{
    public function verify(Request $request)
    {
        $token = $request->input('token');

        $verificationToken = EmailVerificationToken::where('token', $token)->first();

        if (!$verificationToken) {
            return response()->json(['message' => 'Invalid token provided.'], 404);
        }

        if ($verificationToken->expires_at < Carbon::now() || $verificationToken->verified) {
            // Use the most specific HTTP status code for the response
            return response()->json(['message' => 'Token is invalid or has expired.'], 422);
        }

        $user = User::find($verificationToken->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Check for the presence of 'remember_token' in the request
        // and use it if available, otherwise proceed with the existing logic
        $remember_token = $request->input('remember_token');
        if ($remember_token) {
            if ($user->remember_token !== $remember_token) {
                throw new ValidationException("Invalid token provided.");
            }

            $user->remember_token = null; // Clear the remember_token
        }

        $user->email_verified_at = Carbon::now();
        $user->updated_at = Carbon::now(); // Ensure the updated_at timestamp is set
        $user->save();

        $verificationToken->verified = true;
        $verificationToken->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
