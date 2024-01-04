<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\Auth\VerifyEmailController; // Import the VerifyEmailController
use App\Http\Controllers\Auth\AuthController; // Import the AuthController

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

// New route for resetting the user's password
// We are using the new URI as it seems to be the updated one according to the new code guidelines.
Route::post("/users/reset-password", [ResetPasswordController::class, "resetPassword"]);

// Existing route for user registration with throttle middleware
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

// Existing route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// New route for the logout endpoint
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);

// Route for verifying the user's email
// This route is not present in the new code, but we are keeping it as it is part of the existing functionality.
Route::post('/auth/verify-email/{token}', [VerifyEmailController::class, 'verifyEmail'])->middleware('throttle:6,1')->name('verification.verify');

// Route for validating the reset password token
// This route is not present in the new code, but we are keeping it as it is part of the existing functionality.
Route::post('/auth/validate-reset-token', [ResetPasswordController::class, 'validateResetToken']);
