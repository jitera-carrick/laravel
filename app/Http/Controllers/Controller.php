
<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Mail;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    // Existing methods and properties

    protected function sendEmail(string $to, string $subject, string $view, array $data)
    {
        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)
                    ->subject($subject);
        });

        // You may add additional logic here if needed
    }
}
