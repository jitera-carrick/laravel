
<?php

namespace App\Events;

class FailedLogin
{
    public $email;
    public $timestamp;

    public function __construct($email, $timestamp)
    {
        $this->email = $email;
        $this->timestamp = $timestamp;
    }
}
?>
