
<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class TokenHelper
{
    // Generates a unique session token
    public static function generateSessionToken()
    {
        return Str::random(64); // 64 characters
    }

    // Calculates the session expiration time based on the $keepSession flag
    // Returns a Carbon instance representing the expiration time
    public static function calculateSessionExpiration($keepSession)
    {
        return $keepSession ? now()->addDays(90) : now()->addHours(24);
    }
}
