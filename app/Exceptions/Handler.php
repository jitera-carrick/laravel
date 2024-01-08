
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
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
            if ($e instanceof ValidationException) {
                $errors = $e->validator->errors()->getMessages();
                if (isset($errors['token']) && str_contains($errors['token'][0], 'expired')) {
                    // Custom logic for logging email verification token errors
                    \Log::info('Email verification token validation failed: ' . $errors['token'][0]);
                }
            }
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/email/verify/*') && $e->validator->errors()->has('token')) {
                return response()->json([
                    'message' => 'The email verification link is invalid or has expired.',
                    'errors' => $e->validator->errors(),
                ], 422);
            }
        });
    }
}
