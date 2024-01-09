
<?php

use App\Models\User;
use Carbon\Carbon;

class FailedLogin
{
    public $email;
    public $timestamp;

    public function __construct($email, Carbon $timestamp)
    {
        $this->email = $email;
        $this->timestamp = $timestamp;
    }

    // ... rest of the class
}
