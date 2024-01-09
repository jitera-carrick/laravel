<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Models\User;
use App\Models\PasswordResetRequest as PasswordResetRequestModel;
use App\Http\Responses\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class PasswordResetRequestController extends Controller
{
    public function store(Request $request)
    {
        $email = $request->input('email');

        if (empty($email)) {
            return ApiResponse::error('Email is required.', 400);
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return ApiResponse::unauthorized('Email not found.');
        }

        $token = Str::random(60);
        $passwordResetRequest = PasswordResetRequestModel::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addHours(2),
            'status' => 'pending'
        ]);

        // TODO: Send email with the token

        return ApiResponse::success('Password reset request sent.', ['reset_token' => $token], 201);
    }
}
