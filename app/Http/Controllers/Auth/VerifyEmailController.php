<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken; // Existing line kept
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\VerifyEmailRequest;
use App\Repositories\EmailVerificationTokenRepository;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;
use App\Exceptions\EmailVerificationFailedException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator; // Import the Validator facade
use Symfony\Component\HttpFoundation\Response;

class VerifyEmailController extends Controller
{
    public function verify(VerifyEmailRequest $request)
    {
        // This method is no longer needed as we are using POST request for email verification
        // Keeping it for backward compatibility or in case it's used elsewhere
    }

    public function verifyEmail(Request $request, $token = null) // Allow token to be passed or retrieved from the request
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], Response::HTTP_BAD_REQUEST);
        }

        // Use the token from the URL if it's provided, otherwise use the one from the request body
        $token = $token ?: $request->input('token');

        try {
            return DB::transaction(function () use ($token) {
                $tokenRepository = new EmailVerificationTokenRepository();
                $emailVerificationToken = $tokenRepository->findByToken($token);

                if (!$emailVerificationToken) {
                    return response()->json(['message' => 'The verification token is invalid.'], Response::HTTP_NOT_FOUND);
                }

                if ($emailVerificationToken->used) {
                    // Merged the two different messages into one for consistency
                    return response()->json(['message' => 'The verification token is invalid or has already been used.'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if ($emailVerificationToken->expires_at < Carbon::now()) {
                    return response()->json(['message' => 'The verification token is expired.'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $user = $emailVerificationToken->user;
                $user->email_verified_at = Carbon::now();
                $user->save();

                $emailVerificationToken->used = true;
                $emailVerificationToken->save();

                // Merged the two different success messages into one for consistency
                return response()->json(['status' => 200, 'message' => 'Email verification successful.'], Response::HTTP_OK);
            }, 5);
        } catch (EmailVerificationFailedException $e) {
            return new ErrorResource(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return new ErrorResource(['message' => 'An unexpected error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
