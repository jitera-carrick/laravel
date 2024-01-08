<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EmailVerificationToken;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $token)
    {
        // Find the user by the verification token
        $verificationToken = EmailVerificationToken::where('token', $token)
                            ->where('expires_at', '>', Carbon::now())
                            ->where('used', false)
                            ->first();

        if (!$verificationToken) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        $user = User::where('remember_token', $verificationToken->token)->first();

        // Invalidate the token after use
        $verificationToken->used = true;
        $verificationToken->save();

        // If the token is invalid or expired
        if (!$user) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        // Update the user's email verification status
        $user->email_verified_at = Carbon::now();
        $user->save(); // Persist the changes to the database
        $user->save();

        // Return a success response
        return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], 200);
    }
}
