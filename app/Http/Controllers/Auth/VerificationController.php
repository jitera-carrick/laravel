<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class VerificationController extends Controller
{
    public function verify(Request $request)
    {
        $email = $request->input('email');
        $rememberToken = $request->input('remember_token');

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        if ($rememberToken !== $user->remember_token) {
            return response()->json(['error' => 'Invalid token.'], 401);
        }

        $user->email_verified_at = Carbon::now();
        $user->remember_token = null;
        $user->save();

        return response()->json(['message' => 'Email has been successfully verified.'], 200);
    }

    public function verifyEmailSetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|exists:password_reset_requests,token',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
                'different:email'
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->first()], 422);
        }

        $validatedData = $validator->validated();

        $token = $validatedData['token'];
        $password = $validatedData['password'];

        $passwordResetRequest = PasswordResetRequest::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (!$passwordResetRequest) {
            return response()->json(['error' => 'Invalid or expired token.'], 400);
        }

        $user = User::find($passwordResetRequest->user_id);
        if (!$user) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $user->email_verified_at = Carbon::now();
        $user->password = Hash::make($password);
        $user->save();

        $passwordResetRequest->status = 'completed';
        $passwordResetRequest->save();

        return response()->json(['message' => 'Email verified and password set successfully.'], 200);
    }
}
