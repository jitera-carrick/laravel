<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailVerificationToken;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class VerifyEmailController extends Controller
{
    public function verify(Request $request)
    {
        $token = $request->input('token');

        $verificationToken = EmailVerificationToken::where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->where('verified', false)
            ->first();

        if (!$verificationToken) {
            return Response::json(['message' => 'Invalid or expired email verification token.'], 422);
        }

        $user = User::find($verificationToken->user_id);
        if (!$user) {
            return Response::json(['message' => 'User not found.'], 404);
        }

        $user->email_verified_at = Carbon::now();
        $user->save();

        $verificationToken->verified = true;
        $verificationToken->save();

        return Response::json(['message' => 'Email verified successfully.'], 200);
    }
}
