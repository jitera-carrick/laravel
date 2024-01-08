
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
        $token = $request->route('token');

        $verificationToken = EmailVerificationToken::where('token', $token)
                            ->where('expires_at', '>', Carbon::now())
                            ->where('used', false)
                            ->first();

        if (!$verificationToken) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        $user = $verificationToken->user;

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Update the user's email verification status
        $user->email_verified_at = Carbon::now();
        $user->remember_token = null; // Clear the verification token
        $user->save();

        // Mark the token as used
        $verificationToken->used = true;
        $verificationToken->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
