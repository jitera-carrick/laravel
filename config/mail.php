<?php

return [
    // ... (rest of the mail configuration remains unchanged)

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@yourdomain.com'), // Updated the default 'from' address
        'name' => env('MAIL_FROM_NAME', 'Your Application Name'), // Updated the default 'from' name
    ],

    // ... (rest of the mail configuration remains unchanged)
];
