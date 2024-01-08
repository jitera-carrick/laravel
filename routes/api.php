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
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api")->name('register');

// Route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// Route to maintain the session
Route::post('/session/maintain', [SessionController::class, 'maintainSession']);

// Route to update user profile
Route::middleware('auth:sanctum')->put('/users/{user}/profile', [UserController::class, 'updateUserProfile'])
    ->name('users.update.profile');

// Route to update shop information
// The new code has a route for updating shop information, but it lacks validation and authorization checks.
// We will add the necessary validation and authorization within the ShopController's updateShopInfo method.
Route::middleware('auth:sanctum')->put('/shop/info', [ShopController::class, 'updateShopInfo'])
    ->name('shops.update.info');

// Route for user logout
Route::middleware('auth:sanctum')->post('/logout', [LogoutController::class, 'logout']);
