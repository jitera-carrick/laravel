<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\RequestImageController;
use App\Http\Controllers\HairStylistRequestController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\VerifyEmailController;

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

// Route to maintain the session with auth:sanctum middleware
Route::middleware('auth:sanctum')->post('/session/maintain', [SessionController::class, 'maintainSession'])->middleware('throttle:api');

// New POST route for creating hair stylist requests
Route::middleware('auth:sanctum')->post('/hair-stylist-requests', [HairStylistRequestController::class, 'createHairStylistRequest']);

// Route to handle the DELETE request for the endpoint `/api/user/hair-stylist-request/image`
Route::middleware('auth:sanctum')->delete('/user/hair-stylist-request/image', [RequestImageController::class, 'deleteRequestImage']);

// Existing route to handle the DELETE request for the endpoint `/api/requests/images/{request_image_id}`
Route::middleware('auth:sanctum')->delete('/requests/images/{request_image_id}', [RequestImageController::class, 'deleteRequestImage']);

// New POST route for user logout
Route::middleware('auth:sanctum')->post('/logout', [LogoutController::class, 'logout']);

// Add a new GET route for email verification
Route::get('/email/verify/{token}', [VerifyEmailController::class, 'verify'])
    ->name('api.email.verify');

// New PUT route for updating user profile
Route::middleware('auth:sanctum')->put('/user/profile', [UserController::class, 'editUserProfile']);
Route::middleware('auth:sanctum')->patch('/user/profile', [UserController::class, 'editUserProfile']);

// ... other routes ...
