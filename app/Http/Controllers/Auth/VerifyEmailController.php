<?php

use App\Http\Requests\VerifyEmailRequest;
use App\Services\EmailVerificationService;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;

class VerifyEmailController extends Controller
{
    public function verify(VerifyEmailRequest $request)
    {
        try {
            $token = $request->route('token') ?: $request->input('token');

            $verificationService = new EmailVerificationService();
            $verificationResult = $verificationService->verifyToken($token);

            if (!$verificationResult) {
                return (new ErrorResource(['message' => 'Invalid verification link.']))
                    ->response()
                    ->setStatusCode(Response::HTTP_NOT_FOUND);
            }

            if ($verificationResult->isExpired()) {
                return (new ErrorResource(['message' => 'The verification link has expired.']))
                    ->response()
                    ->setStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Assuming the verifyToken method marks the email as verified
            return (new SuccessResource(['message' => 'Email verification successful.']))
                ->response()
                ->setStatusCode(Response::HTTP_OK);

        } catch (ValidationException $e) {
            return (new ErrorResource(['message' => $e->getMessage()]))
                ->response()
                ->setStatusCode(Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return (new ErrorResource(['message' => $e->getMessage()]))
                ->response()
                ->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
