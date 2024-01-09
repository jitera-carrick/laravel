<?php

use App\Models\User;

class FailedLogin
{
    public $user;
    public $reason;
    public $email;
    public $timestamp;

    public function __construct($user, $reason = null, $email = null, $timestamp = null)
    {
        if ($user instanceof User) {
            $this->user = $user;
            $this->reason = $reason;
        } else {
            $this->email = $user; // Assuming the first parameter is email if not an instance of User
            $this->timestamp = $reason; // Assuming the second parameter is timestamp if first is not User
        }
    }

    // ... rest of the class
}
