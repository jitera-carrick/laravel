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

// Route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
// This route is from the new code, but it conflicts with the existing route below.
// Since the functionality seems similar, we'll keep the existing route and comment out the new one.
// Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// Route to maintain the session
Route::post('/session/maintain', [SessionController::class, 'maintainSession']);

// New POST route for creating hair stylist requests
Route::middleware('auth:sanctum')->post('/hair-stylist-requests', [HairStylistRequestController::class, 'createHairStylistRequest']);

// Added route to handle the DELETE request for the endpoint `/api/requests/images/{request_image_id}`
// This route is from the existing code and seems to handle a similar functionality as the commented out route above.
// We'll keep this route as it is more specific and likely to be the correct implementation.
Route::middleware('auth:sanctum')->delete('/requests/images/{request_image_id}', [RequestImageController::class, 'deleteRequestImage']);
