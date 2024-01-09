
<?php

use App\Models\User;

class FailedLogin
{
    public $email;
    public $timestamp;

    public function __construct($email, $timestamp)
    {
        $this->email = $email;
        $this->timestamp = $timestamp;
    }

    // ... rest of the class
}
