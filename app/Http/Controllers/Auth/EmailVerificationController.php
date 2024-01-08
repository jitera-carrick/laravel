
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EmailVerificationToken;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $token)
    {
        // Find the user by the verification token
        $verificationToken = EmailVerificationToken::where('token', $token)
                            ->where('expires_at', '>', Carbon::now())
                            ->where('verified', false) // Changed from 'used' to 'verified'
                            ->first();

        if (!$verificationToken) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        $user = User::where('remember_token', $verificationToken->token)->first();

        // Invalidate the token after use
        $verificationToken->used = true; // This line should be updated if the column name in the database has changed to 'verified'
        $verificationToken->save();

        // If the token is invalid or expired
        if (!$user) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        // Update the user's email verification status
        $user->email_verified_at = Carbon::now();
        $user->save(); // Persist the changes to the database

        // Return a success response
        return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], 200);
    }
}
