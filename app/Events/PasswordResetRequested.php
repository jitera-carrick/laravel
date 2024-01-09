
<?php

namespace App\Events;

use App\Models\PasswordResetRequest;

class PasswordResetRequested
{
    public $passwordResetRequest;

    public function __construct(PasswordResetRequest $passwordResetRequest)
    {
        $this->passwordResetRequest = $passwordResetRequest;
    }
}
