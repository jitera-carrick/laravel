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

// Route for resetting the user's password
Route::post("/users/reset-password", [ResetPasswordController::class, "resetPassword"]);

// Route for user login
Route::post("/login", [AuthController::class, "login"]);

// Route for user registration with throttle middleware
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

// Route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// Route to maintain the session
Route::post('/session/maintain', [SessionController::class, 'maintainSession']);

// Route for validating the password reset token
Route::post('/password-reset/validate-token', [ResetPasswordController::class, 'validateResetToken']);

// Route for verifying the user's email address
Route::post('/auth/email/verify/{token}', [VerifyEmailController::class, 'verify'])->middleware('api');

// Route for sending the password reset link email
Route::post('/auth/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('api');

// New route for resetting the password with middleware
// Note: This route is similar to the existing "/users/reset-password" route.
// If the middleware 'ResetPasswordRequest' is required for the new reset password functionality,
// it should be added to the existing route instead of creating a duplicate route.
Route::post('/users/reset-password', [ResetPasswordController::class, 'resetPassword'])->middleware('ResetPasswordRequest');
