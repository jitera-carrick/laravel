<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Models\User;
use App\Models\PasswordResetRequest as PasswordResetRequestModel;
use App\Notifications\PasswordResetNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(PasswordResetRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        $user = User::where('email', $validatedData['email'])->first();

        if ($user) {
            $resetToken = Str::random(60);
            $tokenExpiration = Carbon::now()->addMinutes(config('auth.passwords.users.expire'));

            $passwordResetRequest = PasswordResetRequestModel::create([
                'user_id' => $user->id,
                'reset_token' => $resetToken,
                'token_expiration' => $tokenExpiration,
                'status' => 'pending',
            ]);

            $user->notify(new PasswordResetNotification($resetToken));
        }

        return response()->json(['message' => 'If your email address is registered in our system, you will receive a password reset link shortly.']);
    }

    public function sendPasswordResetRequest(PasswordResetRequest $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first('email')], 400);
        }

        $validatedData = $validator->validated();
        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found.'], 404);
        }

        $resetToken = Str::random(60);
        $tokenExpiration = Carbon::now()->addMinutes(config('auth.passwords.users.expire'));

        $passwordResetRequest = PasswordResetRequestModel::create([
            'user_id' => $user->id,
            'reset_token' => $resetToken,
            'token_expiration' => $tokenExpiration,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Password reset request sent successfully.',
            'reset_token' => $resetToken
        ], 201);
    }

    // ... other methods ...
}
