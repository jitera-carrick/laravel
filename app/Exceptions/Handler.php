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
            // Handle custom exceptions related to password reset errors
            if ($e instanceof InvalidEmailException) {
                return new JsonResponse([
                    'message' => 'The provided email is invalid.',
                    'error' => $e->getMessage(),
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            if ($e instanceof PasswordPolicyException) {
                return new JsonResponse([
                    'message' => 'The password does not meet the required policy.',
                    'error' => $e->getMessage(),
                ], JsonResponse::HTTP_BAD_REQUEST);
            }

            if ($e instanceof ExpiredTokenException) {
                return new JsonResponse([
                    'message' => 'The password reset token has expired.',
                    'error' => $e->getMessage(),
                ], JsonResponse::HTTP_NOT_FOUND);
            }
        });
    }
}

// Custom exceptions for password reset errors
class InvalidEmailException extends \Exception
{
    // Custom exception for invalid email errors
}

class PasswordPolicyException extends \Exception
{
    // Custom exception for password policy errors
}

class ExpiredTokenException extends \Exception
{
    // Custom exception for expired token errors
}
