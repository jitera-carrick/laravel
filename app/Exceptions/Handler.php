<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

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
            //
        });

        $this->renderable(function (PasswordResetTokenInvalidException $e, $request) {
            return $this->handlePasswordResetTokenInvalidException($e, $request);
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->validator->getMessageBag(),
                ], 422);
            }
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'This action is unauthorized.',
                ], 403);
            }
        });
    }

    /**
     * Handle the PasswordResetTokenInvalidException.
     *
     * @param PasswordResetTokenInvalidException $exception
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handlePasswordResetTokenInvalidException(PasswordResetTokenInvalidException $exception, $request): JsonResponse
    {
        return response()->json([
            'message' => 'The password reset token is invalid or has expired.'
        ], 422);
    }
}
