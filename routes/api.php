<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\AuthController; // Import the AuthController

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
// The URI has been updated to match the guideline '/api/auth/reset-password'
Route::post('/api/auth/reset-password', [ResetPasswordController::class, 'resetPassword']);

// New route for user registration with throttle middleware
// The URI has been updated to match the guideline '/auth/register'
Route::post("/auth/register", [RegisterController::class, "register"])->middleware("throttle:api");

// New route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// New route for logging out the user
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);

// Note: The validation logic for the routes should be handled in the controller or request validation layer, not in the routes file.
