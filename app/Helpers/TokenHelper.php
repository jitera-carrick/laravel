
<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class TokenHelper
{
    public static function generateSessionToken()
    {
        return Str::random(64); // 64 characters
    }

    public static function calculateSessionExpiration($keepSession)
    {
        return $keepSession ? now()->addDays(90) : now()->addHours(24);
    }
}
