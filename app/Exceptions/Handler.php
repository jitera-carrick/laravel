
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $errors = $e->validator->errors()->getMessages();
                if (isset($errors['token']) && str_contains($errors['token'][0], 'expired')) {
                    \Log::info('Email verification token validation failed: ' . $errors['token'][0]);
                }
            }
            if ($e instanceof \App\Exceptions\SessionExpiredException) {
                \Log::info('Session expired: ' . $e->getMessage());
                // Optionally, additional actions such as notifying the user or triggering a re-authentication flow can be added here.
                // For example:
                // $this->notifyUser($e->getUserId());
                // $this->triggerReAuthentication();
            }
        });
    }
}
