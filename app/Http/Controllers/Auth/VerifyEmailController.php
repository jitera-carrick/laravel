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
        // The old code for verifying via user_id and remember_token is kept for backward compatibility
        $user_id = $request->input('user_id');
        $remember_token = $request->input('remember_token');

        if ($user_id && $remember_token) {
            $user = User::find($user_id);

            abort_if(!$user, 404, "User not found.");

            if ($user->remember_token !== $remember_token) {
                throw new ValidationException("Invalid token provided.");
            }

            $user->email_verified_at = Carbon::now();
            $user->remember_token = null;
            $user->updated_at = Carbon::now();
            $user->save();

            return response()->json(['message' => 'Email verified successfully.'], 200);
        }

        // New code for verifying via token
        $token = $request->input('token'); // Retrieve the token from the URL parameter

        if ($token) {
            $verificationToken = EmailVerificationToken::where('token', $token)
                                ->where('used', false)
                                ->where('expires_at', '>', now())
                                ->first();

            if (!$verificationToken) {
                return response()->json(['message' => 'Invalid or expired token.'], 400);
            }

            $user = User::find($verificationToken->user_id);
            if ($user) {
                $user->email_verified_at = now();
                $user->save();

                $verificationToken->used = true;
                $verificationToken->save();

                return response()->json(['message' => 'Email verified successfully.'], 200);
            }

            return response()->json(['message' => 'User not found.'], 404);
        }

        return response()->json(['message' => 'No verification method provided.'], 400);
    }
}
