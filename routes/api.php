<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController; // Import the AuthController
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\SessionController; // Import the SessionController
use App\Http\Controllers\RequestImageController; // Import the RequestImageController
use App\Http\Controllers\HairStylistRequestController; // Import the HairStylistRequestController

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

// Route to maintain the session
Route::post('/session/maintain', [SessionController::class, 'maintainSession']);

// New POST route for creating hair stylist requests
Route::middleware('auth:sanctum')->post('/hair-stylist-requests', [HairStylistRequestController::class, 'createHairStylistRequest']);

// New DELETE route for deleting a request image by ID
// Updated to match the requirement endpoint and added validation
Route::middleware('auth:sanctum')->delete('/request_images/{id}', [RequestImageController::class, 'delete'])
    ->where('id', '[0-9]+')
    ->name('request_images.delete');

// Existing DELETE route for deleting a request image by request_image_id
// This route is kept for backward compatibility or other business logic that might be using it.
Route::middleware('auth:sanctum')->delete('/requests/images/{request_image_id}', [RequestImageController::class, 'deleteRequestImage']);
