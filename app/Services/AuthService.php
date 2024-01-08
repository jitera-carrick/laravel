
<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthService
{
    public function findUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function verifyPassword($user, $password)
    {
        return Hash::check($password, $user->password_hash);
    }

    public function generateSessionToken(User $user)
    {
        $sessionToken = Str::random(60);
        $hashedToken = Hash::make($sessionToken);
        $user->forceFill([
            'session_token' => $hashedToken,
            'session_expiration' => Carbon::now()->addMinutes(config('session.lifetime')),
            'is_logged_in' => true,
        ])->save();

        return $sessionToken;
    }
}
