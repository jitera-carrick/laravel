
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
                    // Custom logic for logging email verification token errors
                    \Log::info('Email verification token validation failed: ' . $errors['token'][0]);
                }
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($e instanceof RequestImageDeletionException) {
                return response()->json([
                    'message' => 'Error occurred during the deletion of the request image.',
                    'error' => $e->getMessage()
                ], 422);
            }
        });
    }
}
