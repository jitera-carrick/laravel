
<?php

namespace App\Exceptions;

use Exception;

class SessionExpiredException extends Exception
{
    protected $message = 'Session has expired.';
    protected $code = 401;
}
