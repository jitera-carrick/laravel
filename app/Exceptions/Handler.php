<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;
use Psr\Log\LoggerInterface;

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
            // Log the exception details for debugging purposes.
            app(LoggerInterface::class)->error($e->getMessage(), [
                'exception' => $e,
            ]);
        });

        $this->renderable(function (Throwable $e, $request) {
            // Check if the exception is related to password policy update process
            if ($e instanceof PasswordPolicyException) {
                // Provide a user-friendly error message in case of a failure
                return new JsonResponse([
                    'message' => 'There was an error updating the password policy. Please try again later.',
                    'error' => $e->getMessage(),
                ], JsonResponse::HTTP_BAD_REQUEST);
            }
        });
    }
}

// Assuming PasswordPolicyException is a custom exception that would be thrown during the password policy update process
class PasswordPolicyException extends \Exception
{
    // Custom exception for password policy update failures
}
