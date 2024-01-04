<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\VerifyEmailRequest;
use App\Repositories\EmailVerificationTokenRepository;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;
use App\Exceptions\EmailVerificationFailedException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmailController extends Controller
{
    public function verify(VerifyEmailRequest $request)
    {
        // This method is no longer needed as we are using POST request for email verification
        // Keeping it for backward compatibility or in case it's used elsewhere
    }

    public function verifyEmail(Request $request)
    {
        try {
            $email = $request->input('email');
            $token = $request->input('token') ?? $request->query('token');
            return DB::transaction(function () use ($token, $email) {
                $tokenRepository = new EmailVerificationTokenRepository(); // Keep the repository from existing code
                $emailVerificationToken = EmailVerificationToken::where('token', $token)
                    ->where('used', false)
                    ->where('expires_at', '>', Carbon::now())
                    ->first();

                if (!$emailVerificationToken) {
                    return response()->json(['message' => 'The verification token is invalid.'], Response::HTTP_NOT_FOUND);
                }

                if ($emailVerificationToken->used) {
                    return response()->json(['message' => 'The verification token is invalid.'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if ($emailVerificationToken->expires_at < Carbon::now()) {
                    return response()->json(['message' => 'The verification token is expired.'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                // Use the user from the token if available, otherwise find by email
                $user = $emailVerificationToken->user ?? User::where('email', $email)->first();
                $user->email_verified_at = Carbon::now();
                $user->save();

                $emailVerificationToken->used = true;
                $emailVerificationToken->save();

                return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], Response::HTTP_OK);
            }, 5); // Keep the retry times from existing code
        } catch (EmailVerificationFailedException $e) {
            return new ErrorResource(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return new ErrorResource(['message' => 'An unexpected error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
