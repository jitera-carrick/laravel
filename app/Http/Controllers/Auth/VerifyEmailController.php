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
    public function verify(Request $request, $token) // Updated to include $token as a route parameter
    {
        // The token is now retrieved from the route parameter instead of request body
        $verificationToken = EmailVerificationToken::where('token', $token)->first();

        if (!$verificationToken) {
            return response()->json(['message' => 'Invalid verification token.'], 404);
        }

        if ($verificationToken->verified) {
            return response()->json(['message' => 'Email is already verified.'], 422);
        }

        if (Carbon::now()->greaterThan($verificationToken->expires_at)) {
            return response()->json(['message' => 'The verification token is expired.'], 422);
        }

        $user = User::find($verificationToken->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        try {
            $user->email_verified_at = Carbon::now();
            $user->save();

            $verificationToken->verified = true;
            $verificationToken->save();

            return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], 200); // Updated the response format to match the requirement
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to verify email.'], 500);
        }
    }
}
