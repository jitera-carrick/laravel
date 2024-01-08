<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VerifyEmailController extends Controller
{
    public function verify(Request $request)
    {
        $token = $request->route('token');

        if (!$token) {
            return response()->json(['message' => 'Invalid or expired verification token.'], 400);
        }

        $verificationToken = EmailVerificationToken::where('token', $token)
                            ->where('expires_at', '>', Carbon::now())
                            ->first();

        if (!$verificationToken) {
            return response()->json(['message' => 'Invalid or expired verification token.'], 404);
        }

        $user = $verificationToken->user;

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($verificationToken->verified) {
            return response()->json(['message' => 'Invalid or expired verification token.'], 410);
        }

        $user->email_verified_at = Carbon::now();
        $user->remember_token = null;
        $user->save();

        $verificationToken->verified = true;
        $verificationToken->save();

        return response()->json(['status' => 200, 'message' => 'Email has been successfully verified.'], 200);
    }
}
