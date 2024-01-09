
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Models\User;
use App\Models\PasswordResetRequest as PasswordResetRequestModel;
use App\Notifications\PasswordResetNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PasswordResetRequestController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\PasswordResetRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PasswordResetRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $resetToken = Hash::make(Str::random(60));
            $tokenExpiration = Carbon::now()->addMinutes(config('auth.passwords.users.expire'));

            $passwordResetRequest = new PasswordResetRequestModel([
                'user_id' => $user->id,
                'reset_token' => $resetToken,
                'token_expiration' => $tokenExpiration,
                'status' => 'pending',
            ]);

            $passwordResetRequest->save();

            $user->notify(new PasswordResetNotification($resetToken));
        }

        return response()->json(['message' => 'If your email is registered, you will receive a password reset email.']);
    }
}
