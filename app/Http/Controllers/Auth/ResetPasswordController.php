<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\PasswordResetToken;
use App\Models\User;

class ResetPasswordController extends Controller
{
    public function reset(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Start a transaction
        DB::beginTransaction();
        try {
            // Retrieve the password reset token entry
            $tokenData = PasswordResetToken::where('email', $request->email)
                ->where('token', $request->token)
                ->first();

            if (!$tokenData) {
                return response()->json(['message' => 'This password reset token is invalid.'], 404);
            }

            // Check if the token has expired
            $tokenLifetime = config('auth.passwords.users.expire');
            if ($tokenData->created_at->addMinutes($tokenLifetime) < now()) {
                return response()->json(['message' => 'This password reset token has expired.'], 404);
            }

            // Retrieve the user by email
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json(['message' => 'User does not exist.'], 404);
            }

            // Update the user's password
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the token
            $tokenData->delete();

            // Commit the transaction
            DB::commit();

            return response()->json(['message' => 'Password has been successfully reset.']);
        } catch (\Exception $e) {
            // Rollback the transaction
            DB::rollBack();
            return response()->json(['message' => 'Failed to reset password.'], 500);
        }
    }
}
