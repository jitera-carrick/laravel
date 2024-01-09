
<?php

namespace App\Helpers;

class TokenHelper
{
    public static function generateSessionToken(): string
    {
        return bin2hex(random_bytes(32)); // 32 bytes = 256 bits
    }

    public static function calculateSessionExpiration($keepSession)
    {
        // Assuming $keepSession is a boolean
        return $keepSession ? now()->addDays(90) : now()->addHours(24); // Returns a DateTime instance
    }
}
