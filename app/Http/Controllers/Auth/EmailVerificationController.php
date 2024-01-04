
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\VerifyEmailRequest;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use App\Repositories\EmailVerificationTokenRepository;
use App\Repositories\UserRepository;
use App\Exceptions\EmailVerificationFailedException;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $token)
    {
        $tokenRepository = new EmailVerificationTokenRepository();
        $userRepository = new UserRepository();
        $emailVerificationToken = $tokenRepository->findActiveToken($token);

        try {
            if (!$emailVerificationToken) {
                throw new EmailVerificationFailedException('Invalid or expired token.');
            }

            $user = $userRepository->find($emailVerificationToken->user_id);
            if (!$user) {
                throw new EmailVerificationFailedException('User not found.');
            }

            $user->email_verified_at = Carbon::now();
            $user->save();

            $emailVerificationToken->used = true;
            $emailVerificationToken->save();

            return new SuccessResource(['message' => 'Email verified successfully.']);
        } catch (EmailVerificationFailedException $exception) {
            return new ErrorResource(['message' => $exception->getMessage()]);
        } catch (\Exception $exception) {
            return new ErrorResource(['message' => 'An error occurred during email verification.']);
        }
    }
}
