
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
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                \Log::warning('Failed login attempt: ' . $e->getMessage());

                $response = response()->json([
                    'message' => 'Login failed. Please check your credentials and try again or reset your password.',
                    'error' => $e->getMessage(),
                ], 401);

                $this->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) use ($response) {
                    return $response;
                });
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $errors = $e->validator->errors()->getMessages();
                if (isset($errors['token']) && str_contains($errors['token'][0], 'expired')) {
                    // Custom logic for logging email verification token errors
                    \Log::info('Email verification token validation failed: ' . $errors['token'][0]);
                }
            }
        });
    }
}
