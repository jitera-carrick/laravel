
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Carbon\Carbon;

class ValidDate implements Rule
{
    public function passes($attribute, $value)
    {
        $date = Carbon::createFromFormat('Y-m-d', $value);
        return $date && $date->isFuture();
    }

    public function message()
    {
        return 'The :attribute must be a valid date and not in the past.';
    }
}
