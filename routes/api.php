<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController; // Import the AuthController
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\Auth\ForgotPasswordController; // Import the ForgotPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\SessionController; // Import the SessionController
use App\Http\Controllers\VerifyEmailController; // Import the VerifyEmailController
use App\Http\Controllers\Auth\LoginController; // Import the LoginController

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned to the "api" middleware group. Enjoy building your API!
|
*/

// Existing route for getting the authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route for resetting the user's password with middleware
Route::post("/users/reset-password", [ResetPasswordController::class, "resetPassword"])
    ->middleware('ResetPasswordRequest')
    ->middleware('validate:reset-password'); // Added middleware for validation from new code

// Route for user login
// The new code has a similar route with 'api' middleware.
// To resolve the conflict, we keep the existing route and add the middleware from the new code.
Route::post("/login", [AuthController::class, "login"])->middleware('api');

// Route for user registration with throttle middleware and validation
Route::post("/auth/register", [RegisterController::class, "register"])
    ->middleware("throttle:api")
    ->middleware('validate:register'); // Added middleware for validation from new code

// Route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// Route to maintain the session
Route::post('/session/maintain', [SessionController::class, 'maintainSession']);

// Route for validating the password reset token
Route::middleware('auth:sanctum')->post('/password-reset/validate-token', [ResetPasswordController::class, 'validateResetToken'])
    ->name('password.validate-token');

// Route for verifying the user's email address
Route::post('/auth/email/verify/{token}', [VerifyEmailController::class, 'verify'])->middleware('api');

// Route for sending the password reset link email
Route::post('/auth/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('api');

// New route for user login with 'api' middleware
// This route is redundant because we have already modified the existing login route to include the 'api' middleware.
// Therefore, we do not need to add this route again.
// Route::post('/auth/login', [LoginController::class, 'login'])->middleware('api');
