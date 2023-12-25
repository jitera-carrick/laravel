<?php

return [
    // ... (other configuration remains unchanged)

    /*
    |--------------------------------------------------------------------------
    | Additional Mail Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may specify additional configuration for the password reset
    | emails, such as using a specific mailer or queue.
    |
    */

    'password_reset' => [
        'mailer' => env('PASSWORD_RESET_MAILER', 'smtp'),
        'queue' => env('PASSWORD_RESET_QUEUE', null),
    ],
];
