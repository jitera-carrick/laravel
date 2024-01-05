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
    public function verify(Request $request, $token = null) // Allow $token to be optional
    {
        if ($token) {
            // The token is retrieved from the route parameter
            $verificationToken = EmailVerificationToken::where('token', $token)->first();
        } else {
            // Validate the request if token is not provided in the route parameter
            $request->validate([
                'email' => 'required|email',
                'token' => 'required|string',
            ]);

            $email = $request->input('email');
            $token = $request->input('token');

            $user = User::where('email', $email)->first();

            if (!$user) {
                return response()->json(['message' => 'Email address not found.'], 404);
            }

            $verificationToken = EmailVerificationToken::where('token', $token)
                ->where('user_id', $user->id)
                ->first();
        }

        if (!$verificationToken) {
            return response()->json(['message' => 'Invalid verification token.'], 404);
        }

        if ($verificationToken->verified) {
            return response()->json(['message' => 'Email is already verified.'], 422);
        }

        if (Carbon::now()->greaterThan($verificationToken->expires_at)) {
            return response()->json(['message' => 'The verification token is expired.'], 422);
        }

        // If the token was passed in the route, find the user by the user_id in the token
        if (!$user) {
            $user = User::find($verificationToken->user_id);
        }

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        try {
            $user->email_verified_at = Carbon::now();
            $user->save();

            $verificationToken->verified = true;
            $verificationToken->save();

            return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to verify email.'], 500);
        }
    }
}
