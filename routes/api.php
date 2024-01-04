<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\RequestController; // Import the RequestController
use App\Http\Requests\CreateHairStylistRequest; // Import the CreateHairStylistRequest validation

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

// Route for user registration with throttle middleware
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

// Route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// Route for creating hair stylist requests
Route::middleware('auth:sanctum')->post('/hair-stylist-requests', [RequestController::class, 'store'])->middleware('throttle:api');

// Updated route for resetting the password as per the guideline
Route::middleware('api')->post('/auth/reset-password', [ResetPasswordController::class, 'resetPassword'])->middleware('throttle:api');

// New POST route for the endpoint `/api/auth/validate-reset-token`
Route::middleware('throttle:api')->post('/auth/validate-reset-token', [ResetPasswordController::class, 'validateResetToken']);
