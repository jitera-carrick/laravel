<?php

namespace App\Services;

use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Helpers\TokenHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Exception;

class PasswordResetService
{
    public function handlePasswordResetRequest($email)
    {
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return [
                'status' => 400,
                'message' => 'Email not found or invalid.'
            ];
        }

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

            // Send password reset email to the user
            Mail::send('emails.password_reset', ['token' => $resetToken], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Password Reset Request');
            });

            DB::commit();
            return [
                'status' => 200,
                'message' => 'Password reset request sent successfully.',
                'reset_token' => $resetToken
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'status' => 500,
                'message' => 'An unexpected error occurred on the server.'
            ];
        }
    }

    public function sendResetLink(string $email)
    {
        $response = $this->handlePasswordResetRequest($email);

        if ($response['status'] !== 200) {
            return $response;
        }

        return [
            'status' => 'success',
            'reset_token' => $response['reset_token']
        ];
    }
}
