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
        $user_id = $request->input('user_id');
        $remember_token = $request->input('remember_token');
        $token = $request->input('token');

        if ($token) {
            // This block is from the existing code
            $verificationToken = EmailVerificationToken::where('token', $token)
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$verificationToken) {
                return response()->json(['message' => 'Invalid or expired email verification token.'], 404);
            }

            $user = $verificationToken->user;
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $user->email_verified_at = Carbon::now();
            $user->save();

            $verificationToken->used = true;
            $verificationToken->save();

            return response()->json(['message' => 'Email verified successfully.'], 200);
        } elseif ($user_id && $remember_token) {
            // This block is from the new code
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function verifyEmailAlternative(Request $request)
    {
        $token = $request->input('token');

        $emailVerificationToken = EmailVerificationToken::where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->where('used', false)
            ->first();

        if (!$emailVerificationToken) {
            throw new ValidationException("Invalid or expired token provided.");
        }

        $emailVerificationToken->used = true;
        $emailVerificationToken->save();

        $user = $emailVerificationToken->user;
        $user->email_verified_at = Carbon::now();
        $user->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
            // This block is from the existing code
            $verificationToken = EmailVerificationToken::where('token', $token)
                ->where('used', false)
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if (!$verificationToken) {
                return response()->json(['message' => 'Invalid or expired email verification token.'], 404);
            }

            $user = $verificationToken->user;
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $user->email_verified_at = Carbon::now();
            $user->save();

            $verificationToken->used = true;
            $verificationToken->save();

            return response()->json(['message' => 'Email verified successfully.'], 200);
        } elseif ($user_id && $remember_token) {
            // This block is from the new code
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function verifyEmailAlternative(Request $request)
    {
        $token = $request->input('token');

        $emailVerificationToken = EmailVerificationToken::where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->where('used', false)
            ->first();

        if (!$emailVerificationToken) {
            throw new ValidationException("Invalid or expired token provided.");
        }

        $emailVerificationToken->used = true;
        $emailVerificationToken->save();

        $user = $emailVerificationToken->user;
        $user->email_verified_at = Carbon::now();
        $user->save();

        return response()->json(['message' => 'Email verified successfully.'], 200);
    }
}
