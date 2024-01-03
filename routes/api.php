<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\UserController; // Import the UserController

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
// This route is duplicated in the new code with a different URI. We will keep the corrected URI.
// Route::post("/users/reset-password", [ResetPasswordController::class, 'resetPassword']); // This line is removed because of duplication

// New route for user registration with throttle middleware
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

// New route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// New route to validate the reset password token
Route::post('/auth/validate-reset-token', [ResetPasswordController::class, 'validateResetToken']);

// Corrected route for the password reset endpoint as per the requirement
Route::post('/api/auth/reset-password', [ResetPasswordController::class, 'resetPassword']);
