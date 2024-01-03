<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Carbon;

class SessionService
{
    // ... (other methods)

    /**
     * Extend the session expiration date if the keep_session flag is true.
     *
     * @param string $sessionToken
     * @return Carbon|null
     * @throws Exception
     */
    public function extendSession($sessionToken)
    {
        $user = User::where('session_token', $sessionToken)->first();

        if (!$user) {
            throw new Exception('User not found.');
        }

        if ($user->session_expiration && $user->session_expiration->gt(Carbon::now())) {
            $newExpirationDate = Carbon::now()->addDays(90);
            $user->session_expiration = $newExpirationDate;
            $user->save();

            return $newExpirationDate;
        }

        throw new Exception('Session cannot be extended.');
    }

    // ... (other methods)
}
