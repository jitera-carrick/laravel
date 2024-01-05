<?php

namespace App\Http\Controllers\Auth;

use App\Models\EmailVerificationToken;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class VerifyEmailController extends Controller
{
    public function verify(Request $request) // Update this method to handle email verification
    {
        $token = $request->input('token');

        $verificationToken = EmailVerificationToken::where('token', $token)->first();

        if (!$verificationToken) {
            return response()->json(['message' => 'Invalid token provided.'], 404);
        }

        if ($verificationToken->expires_at < Carbon::now() || $verificationToken->verified) {
            return response()->json(['message' => 'Token is invalid or has expired.'], 400);
        }

        $user = User::find($verificationToken->user_id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->email_verified_at = Carbon::now();
        $user->save();

        $verificationToken->verified = true;
        $verificationToken->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
