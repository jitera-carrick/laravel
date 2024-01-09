
<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Http\Responses\ApiResponse;

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
                    // Custom logic for logging email verification token errors
                    \Log::info('Email verification token validation failed: ' . $errors['token'][0]);
                }
            }
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return ApiResponse::loginFailure();
        }

        return redirect()->guest(route('login'));
    }
}
