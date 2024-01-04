
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

        // The following lines are no longer needed due to the new token verification logic
        // $user_id = $request->input('user_id');
        // $remember_token = $request->input('remember_token');

        // $user = User::find($user_id);

        // abort_if(!$user, 404, "User not found.");

        // if ($user->remember_token !== $remember_token) {
        //     throw new ValidationException("Invalid token provided.");
        // }

        // $user->email_verified_at = Carbon::now();
        // $user->remember_token = null;
        // $user->updated_at = Carbon::now();
        // $user->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    } // End of verify method
}
