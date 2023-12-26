<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PasswordResetRequest;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\Mail;
use Exception;
use Illuminate\Support\Facades\DB;

class ResetPasswordController extends Controller
{
    /**
     * Validate the password reset token.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateResetToken(Request $request)
    {
        // ... (existing code remains unchanged)
    }

    /**
     * Set a new password for the user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setNewPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string|exists:password_reset_requests,token',
            'password' => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&]).+$/',
        ], [
            'token.required' => 'Token is required.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password does not meet the required policy.',
            'password.regex' => 'Password does not meet the required policy.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 422);
        }

        return DB::transaction(function () use ($request) {
            $passwordResetRequest = PasswordResetRequest::where('token', $request->token)
                ->where('expires_at', '>', now())
                ->first();

            if (!$passwordResetRequest) {
                return response()->json(['message' => 'The password reset token is invalid or has expired.'], 400);
            }

            $user = User::find($passwordResetRequest->user_id);
            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            if (Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'The new password cannot be the same as the current password.'], 400);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            $passwordResetRequest->delete();

            return response()->json(['status' => 200, 'message' => 'Your password has been successfully reset.'], 200);
        });
    }

    public function reset(Request $request)
    {
        // ... (existing code remains unchanged)
    }

    public function verifyEmailAndSetPassword(Request $request)
    {
        // ... (existing code remains unchanged)
    }

    /**
     * Handle the error codes received from the password reset request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function handlePasswordResetError(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'error_code' => 'required|string',
        ], [
            'error_code.required' => 'Error code is required.',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 400);
        }

        $errorCode = $request->error_code;
        $message = 'Error has been handled.';

        switch ($errorCode) {
            // Define cases for recognized error codes
            default:
                return response()->json(['message' => 'Unknown error code.'], 400);
        }

        return response()->json(['status' => 200, 'message' => $message]);
    }
}
