
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $token)
    {
        // Find the user by the verification token
        $user = User::where('remember_token', $token)->first();

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

    /**
     * Verify the user's email using an alternative method.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmailAlternative(Request $request)
    {
        $token = $request->input('token');
        $verificationToken = EmailVerificationToken::where('token', $token)
                            ->where('expires_at', '>', Carbon::now())
                            ->where('used', false)
                            ->first();

        if (!$verificationToken) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        // ... (additional logic to verify the user's email and update the database)
        // Assuming the additional logic is to verify the user's email and update the database
        $user = User::find($verificationToken->user_id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->email_verified_at = Carbon::now();
        $verificationToken->used = true; // Mark the token as used
        $user->save();
        $verificationToken->save();

        return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], 200);
    }
}
