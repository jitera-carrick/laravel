
<?php

use App\Models\User;
use App\Models\LoginAttempt;

class FailedLogin
{
    public $user;
    public $reason;
    public $email;
    public $timestamp;
    public $loginAttempt;

    public function __construct($user, $reason = null, $email = null, $timestamp = null)
    {
        if ($user instanceof User) {
            $this->user = $user;
            $this->reason = $reason;
        } else {
            $this->email = $user; // Assuming the first parameter is email if not an instance of User
            $this->loginAttempt = $reason instanceof LoginAttempt ? $reason : null;
            $this->timestamp = $this->loginAttempt ? $this->loginAttempt->attempted_at : $reason; // Adjusted to use LoginAttempt if provided
        }
    }

    // ... rest of the class
}
