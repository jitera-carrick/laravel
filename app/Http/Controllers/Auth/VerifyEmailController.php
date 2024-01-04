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
use Illuminate\Http\JsonResponse;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;
use App\Exceptions\EmailVerificationFailedException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VerifyEmailController extends Controller
{
    public function verify(VerifyEmailRequest $request)
    {
        // This method is kept for backward compatibility or in case it's used elsewhere
        // Keeping it for backward compatibility or in case it's used elsewhere
    }

    public function verifyEmail(VerifyEmailRequest $request, $token)
    {
        try {
            return DB::transaction(function () use ($token) {
                $tokenRepository = new EmailVerificationTokenRepository(); // Use this repository if it's provided in the guideline or exists in the project
                $emailVerificationToken = $tokenRepository->findByToken($token);

                if (!$emailVerificationToken) {
                    return response()->json(['message' => 'The verification token is invalid.'], Response::HTTP_NOT_FOUND);
                }

                if ($emailVerificationToken->used) {
                    return response()->json(['message' => 'The verification token has already been used.'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if ($emailVerificationToken->expires_at < Carbon::now()) {
                    return response()->json(['message' => 'The verification token is expired.'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $user = $emailVerificationToken->user;
                $user->email_verified_at = Carbon::now(); // Set the current datetime to email_verified_at
                $user->save();

                $emailVerificationToken->used = true;
                $emailVerificationToken->save();

                // Using SuccessResource to maintain consistency with the existing code
                return new SuccessResource(['message' => 'Email verified successfully.'], Response::HTTP_OK);
            }, 5);
        } catch (EmailVerificationFailedException $e) { // Handle specific exceptions first
            // Using the existing code's HTTP_BAD_REQUEST for consistency
            return new ErrorResource(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new ErrorResource(['message' => 'An unexpected error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
