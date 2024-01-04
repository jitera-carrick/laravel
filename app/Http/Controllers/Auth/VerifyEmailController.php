
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

class VerifyEmailController extends Controller
{
    public function verify(VerifyEmailRequest $request)
    {
        $token = $request->input('token');

        return DB::transaction(function () use ($token) {
            $tokenRepository = new EmailVerificationTokenRepository();
            $emailVerificationToken = $tokenRepository->findByToken($token);

            if (!$emailVerificationToken || $emailVerificationToken->used || $emailVerificationToken->expires_at < Carbon::now()) {
                throw new EmailVerificationFailedException("The email verification token is invalid or has expired.");
            }

            $user = $emailVerificationToken->user;
            $user->email_verified_at = Carbon::now();
            $user->save();

            $emailVerificationToken->used = true;
            $emailVerificationToken->save();

            return new SuccessResource(['message' => 'Email verified successfully.']);
        }, 5);
    }
}
