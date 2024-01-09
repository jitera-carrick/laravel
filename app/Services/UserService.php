
<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class UserService
{
    /**
     * Verify user's email using the remember_token.
     *
     * @param string $token
     * @return bool
     */
    public function verifyUserEmail($token)
    {
        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return false;
        }

        $user->update([
            'email_verified_at' => Carbon::now(),
        ]);

        return true;
    }
}
