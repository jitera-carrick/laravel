<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Response;

class ForgotPasswordController extends Controller
{
    // ... (other methods in the controller)

    // Add the new method below
    public function verifyResetToken(Request $request)
    {
        try {
            $email = $request->input('email');
            $token = $request->input('token');

            $passwordResetToken = PasswordResetToken::where('email', $email)
                ->where('token', $token)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->first();

            if ($passwordResetToken) {
                return Response::json([
                    'status' => 'success',
                    'message' => 'Token is valid. You can proceed to reset your password.'
                ]);
            } else {
                return Response::json([
                    'status' => 'error',
                    'message' => 'Invalid or expired token.'
                ], 400);
            }
        } catch (\Exception $e) {
            return Response::json([
                'status' => 'error',
                'message' => 'An error occurred while verifying the token.'
            ], 500);
        }
    }

    // ... (rest of the code in the controller)
}
