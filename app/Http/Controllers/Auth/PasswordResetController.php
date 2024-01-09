
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest as PasswordResetRequestValidation;
use App\Services\AuthService;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use App\Notifications\PasswordResetNotification;

class PasswordResetController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function requestPasswordReset(PasswordResetRequestValidation $request)
    {
        $validatedData = $request->validated();
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return ApiResponse::loginFailure();
        }

        $passwordResetRequest = $this->authService->createPasswordResetRequest($user);
        $user->notify(new PasswordResetNotification($passwordResetRequest));

        return ApiResponse::loginSuccess(['reset_token' => $passwordResetRequest->reset_token]);
    }
}
