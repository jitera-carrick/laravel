<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\Auth\VerifyEmailController; // Import the VerifyEmailController
use App\Http\Controllers\RequestController; // Import the RequestController

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

// Updated route for resetting the user's password to match the guideline
Route::post('/auth/reset-password', [ResetPasswordController::class, 'resetPassword']);

// Existing route for user registration with throttle middleware
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

// Existing route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// Route for verifying the user's email
// Updated to include validation and error handling as per the requirement
Route::post('/auth/verify-email/{token}', [VerifyEmailController::class, 'verifyEmail'])
    ->middleware('throttle:6,1')
    ->name('verification.verify')
    ->where('token', '[A-Za-z0-9]+'); // Ensure 'token' consists of alphanumeric characters

// Route for validating the reset password token
Route::post('/auth/validate-reset-token', [ResetPasswordController::class, 'validateResetToken']);

// New route for updating a hair stylist request
Route::patch('/hair-stylist-requests/{id}', [RequestController::class, 'update'])
    ->middleware('auth:sanctum') // Changed from 'auth:api' to 'auth:sanctum' to match the existing middleware
    ->where('id', '[0-9]+'); // Adding a where condition to ensure 'id' is an integer
