<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
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

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                $message = $e->getMessage();
                if ($e->getCode() === 401) {
                    return ApiResponse::loginFailure($message);
                }
                return ApiResponse::unauthenticated($message);
            }
        });
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            $message = $exception->getMessage();
            return ApiResponse::loginFailure($message);
        }

        return redirect()->guest(route('login'));
    }
}
