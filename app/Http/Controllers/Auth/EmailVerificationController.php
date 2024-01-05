
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\VerifyEmailRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $token)
    {
        // Find the user by the verification token
        $user = User::where('remember_token', $token)->first();

        // Old code for finding user by remember_token is preserved for backward compatibility
        // If the token is invalid or expired
        if (!$user) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        // Update the user's email verification status
        $user->email_verified_at = Carbon::now();
        $user->remember_token = null; // Clear the verification token
        $user->save();

        // Return a success response
        return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], 200);
    }

    public function verifyEmail(VerifyEmailRequest $request)
    {
        $token = $request->input('token');
        $emailVerificationToken = EmailVerificationToken::where('token', $token)->first();

        if (!$emailVerificationToken || $emailVerificationToken->expires_at < Carbon::now() || $emailVerificationToken->verified) {
            return new ErrorResource(['message' => 'Invalid or expired token.']);
        }

        $user = User::find($emailVerificationToken->user_id);
        if (!$user) {
            return new ErrorResource(['message' => 'User not found.']);
        }

        $user->email_verified_at = Carbon::now();
        $user->save();

        $emailVerificationToken->verified = true;
        $emailVerificationToken->save();

        return new SuccessResource(['message' => 'Email verified successfully.']);
    }
}
