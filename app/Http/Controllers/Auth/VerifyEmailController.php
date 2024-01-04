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

        $emailVerificationToken = EmailVerificationToken::where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$emailVerificationToken) {
            return response()->json(['message' => 'Invalid or expired token provided.'], 404);
        }

        $user = User::find($emailVerificationToken->user_id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->email_verified_at = Carbon::now();
        $user->save();

        $emailVerificationToken->used = true;
        $emailVerificationToken->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }

    public function verifyEmailWithToken(Request $request)
    {
        $email = $request->input('email');
        $token = $request->input('token');

        $verificationToken = EmailVerificationToken::where('email', $email)
                            ->where('token', $token)
                            ->where('used', false)
                            ->where('expires_at', '>', now())
                            ->first();

        if (!$verificationToken) {
            return response()->json(['message' => 'Invalid or expired token.'], 422);
        }

        $user = User::find($verificationToken->user_id);
        $user->email_verified_at = Carbon::now();
        $user->save();

        $verificationToken->used = true;
        $verificationToken->save();

        return response()->json(['message' => 'Email verified successfully.']);
    }
}
