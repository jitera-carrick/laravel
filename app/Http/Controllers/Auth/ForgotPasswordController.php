
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Models\User;
use App\Models\PasswordResetRequest as PasswordResetRequestModel;
use App\Helpers\TokenHelper;
use App\Notifications\PasswordResetNotification;

class ForgotPasswordController extends Controller
{
    public function sendPasswordResetLink(PasswordResetRequest $request)
    {
        $validatedData = $request->validated();
        $user = User::where('email', $validatedData['email'])->first();

        if ($user) {
            $resetToken = TokenHelper::generateSessionToken();
            $tokenExpiration = now()->addHour();

            $passwordResetRequest = PasswordResetRequestModel::create([
                'user_id' => $user->id,
                'reset_token' => $resetToken,
                'token_expiration' => $tokenExpiration,
            ]);

            $user->notify(new PasswordResetNotification($resetToken));

            return response()->json(['message' => 'If your email address is registered, a password reset link has been sent to it.'], 200);
        }

        return response()->json(['message' => 'If your email address is registered, a password reset link has been sent to it.'], 200);
    }
}
