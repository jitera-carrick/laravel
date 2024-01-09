<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Responses\ApiResponse;
use App\Models\PasswordResetRequest as ModelsPasswordResetRequest;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    public function createPasswordResetRequest(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'Email is required.',
            'email.exists' => 'Email not found.',
        ]);

        $user = User::where('email', $validatedData['email'])->first();

        if (!$user) {
            return ApiResponse::error('Email not found.', 401);
        }

        $passwordResetRequest = new ModelsPasswordResetRequest();
        $passwordResetRequest->user_id = $user->id;
        $passwordResetRequest->token = Str::random(60);
        $passwordResetRequest->expires_at = now()->addHours(2);
        $passwordResetRequest->status = 'pending';
        $passwordResetRequest->save();

        return ApiResponse::success('Password reset request sent.', ['reset_token' => $passwordResetRequest->token], 201);
    }
}
