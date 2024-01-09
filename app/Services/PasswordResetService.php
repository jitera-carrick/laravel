
<?php

namespace App\Services;

use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Helpers\TokenHelper;
use Illuminate\Support\Facades\DB;
use Exception;

class PasswordResetService
{
    public function handlePasswordResetRequest($email)
    {
        DB::beginTransaction();
        try {
            $user = User::where('email', $email)->firstOrFail();
            $tokenHelper = new TokenHelper();
            $resetToken = $tokenHelper->generateSessionToken();
            $tokenExpiration = $tokenHelper->calculateSessionExpiration(false);

            $passwordResetRequest = new PasswordResetRequest([
                'user_id' => $user->id,
                'reset_token' => $resetToken,
                'token_expiration' => $tokenExpiration,
                'status' => 'pending',
            ]);
            $passwordResetRequest->save();

            DB::commit();
            return $passwordResetRequest;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
