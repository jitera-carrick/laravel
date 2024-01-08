
<?php

use App\Http\Requests\VerifyEmailRequest;
use App\Services\EmailVerificationService;
use App\Http\Resources\SuccessResource;
use App\Http\Resources\ErrorResource;

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\EmailVerificationToken;
// use Illuminate\Http\Request; // Commented out since we are using VerifyEmailRequest
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class VerifyEmailController extends Controller
{
    public function verify(VerifyEmailRequest $request)
    {
        try {
            // Attempt to use the token from the route first
            $token = $request->route('token');

            // If the token is not available in the route, use the token from the request input
            if (!$token) {
                $token = $request->input('token');
            }

            $verificationService = new EmailVerificationService();
            $verificationResult = $verificationService->verifyToken($token);

            return new SuccessResource(['message' => 'Email verified successfully.']);

        } catch (\Exception $e) {
            // Handle the exception and return an error response
            return new ErrorResource(['message' => $e->getMessage()]);
        }
    }
}
