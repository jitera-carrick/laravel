<?php
namespace App\Services\Auth;

use App\Models\User;
use App\Models\PasswordResetRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\PasswordResetTokenInvalidException;

class PasswordResetService
{
    // ... (other methods in the service)

    /**
     * Validate the password reset token.
     *
     * @param string $token
     * @return PasswordResetRequest
     * @throws PasswordResetTokenInvalidException
     */
    public function validateResetToken(string $token): PasswordResetRequest
    {
        $passwordResetRequest = PasswordResetRequest::where('token', $token)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$passwordResetRequest) {
            throw new PasswordResetTokenInvalidException('The password reset token is invalid or has expired.');
        }

        return $passwordResetRequest;
    }

    /**
     * Handle the password reset process.
     *
     * @param array $data
     * @return string
     */
    public function resetPassword(array $data): string
    {
        $validator = Validator::make($data, [
            'email' => 'required|email|exists:users,email',
            'password' => 'required|confirmed|min:8',
            'token' => 'required|exists:password_reset_requests,token',
        ]);

        if ($validator->fails()) {
            return implode("\n", $validator->errors()->all());
        }

        try {
            $passwordResetRequest = $this->validateResetToken($data['token']);
        } catch (PasswordResetTokenInvalidException $e) {
            return $e->getMessage();
        }

        $user = User::where('email', $data['email'])->first();
        $user->password = Hash::make($data['password']);
        $user->save();

        // Delete the password reset request after successful reset
        $passwordResetRequest->delete();

        return 'A password reset email has been sent.';
    }

    // ... (other methods in the service)
}
