<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController; // Import the AuthController
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\SessionController; // Import the SessionController
use App\Http\Controllers\ShopController; // Import the ShopController
use App\Http\Controllers\Auth\LogoutController; // Import the LogoutController

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
// The new code and existing code have duplicate routes for registration. We will keep the named route from the existing code.
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api")->name('register');

// Route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// Route to maintain the session
Route::post('/session/maintain', [SessionController::class, 'maintainSession']);

// Route to update user profile
// This is a new route from the new code that needs to be added.
Route::middleware('auth:sanctum')->put('/users/{user}/profile', [UserController::class, 'updateUserProfile'])
    ->name('users.update.profile');

// Route to update shop information
Route::middleware('auth:sanctum')->match(['put', 'patch'], '/shops/{id}', [ShopController::class, 'updateShop'])
    ->name('shops.update');

// Route for user logout
Route::middleware('auth:sanctum')->post('/logout', [LogoutController::class, 'logout']);
