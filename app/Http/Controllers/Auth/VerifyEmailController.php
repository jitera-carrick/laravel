<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use Illuminate\Http\Request;
use App\Http\Requests\VerifyEmailRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;

class VerifyEmailController extends Controller
{
    public function verify(Request $request)
    {
        $token = $request->input('token');
        $user_id = $request->input('user_id');
        $remember_token = $request->input('remember_token');

        if ($token) {
            try {
                $verificationToken = EmailVerificationToken::where('token', $token)
                    ->where('used', false)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();

                if (!$verificationToken) {
                    throw new ValidationException("Invalid or expired token provided.");
                }

                $user = $verificationToken->user;
                $user->email_verified_at = Carbon::now();
                $user->save();

                $verificationToken->used = true;
                $verificationToken->save();

                return response()->json(['message' => 'Email verified successfully.'], 200);
            } catch (\Exception $e) {
                // Log the exception if needed
                // Log::error($e->getMessage());
                return response()->json(['message' => 'An error occurred during email verification.'], 500);
            }
        } elseif ($user_id && $remember_token) {
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
        } else {
            return response()->json(['message' => 'Invalid request.'], 400);
        }
    }

    /**
     * Verify the user's email using an alternative method.
     *
     * @param VerifyEmailRequest $request
     * @return SuccessResource|ErrorResource
     */
    public function verifyEmailAlternative(VerifyEmailRequest $request)
    {
        $email = $request->input('email');
        $token = $request->input('token');

        $verificationToken = EmailVerificationToken::where('email', $email)
            ->where('token', $token)
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$verificationToken) {
            return new ErrorResource(['message' => 'Invalid or expired token provided.']);
        }

        $verificationToken->user->markEmailAsVerified();
        $verificationToken->used = true;
        $verificationToken->save();

        return new SuccessResource(['message' => 'Email verified successfully.']);
    }
}
