
<?php

namespace App\Http\Helpers;

use Illuminate\Support\Str;
use Carbon\Carbon;

class SessionHelper
{
    public static function generateSessionToken()
    {
        return Str::random(60);
    }

    public static function calculateSessionExpiration($keepSession)
    {
        return $keepSession ? Carbon::now()->addDays(90) : Carbon::now()->addHours(24);
    }
}
