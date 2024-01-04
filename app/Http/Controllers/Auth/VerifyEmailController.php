
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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

    public function verifyEmail(VerifyEmailRequest $request, $token)
    {
        try {
            return DB::transaction(function () use ($token) {
                $tokenRepository = new EmailVerificationTokenRepository();
                $emailVerificationToken = $tokenRepository->findByToken($token);

                if (!$emailVerificationToken) {
                    return response()->json(['message' => 'The verification token is invalid.'], Response::HTTP_NOT_FOUND);
                }

                if ($emailVerificationToken->used) {
                    return response()->json(['message' => 'The verification token is invalid.'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                if ($emailVerificationToken->expires_at < Carbon::now()) {
                    return response()->json(['message' => 'The verification token is expired.'], Response::HTTP_UNPROCESSABLE_ENTITY);
                }

                $user = $emailVerificationToken->user;
                $user->email_verified_at = Carbon::now();
                $user->save();

                $emailVerificationToken->used = true;
                $emailVerificationToken->save();

                return response()->json(['status' => 200, 'message' => 'Email verified successfully.'], Response::HTTP_OK);
            }, 5);
        } catch (EmailVerificationFailedException $e) {
            return new ErrorResource(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return new ErrorResource(['message' => 'An unexpected error occurred.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
