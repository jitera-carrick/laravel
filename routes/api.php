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
// Removed the duplicate route from the new code and updated the existing route to match the requirement.
Route::post("/auth/register", [RegisterController::class, "register"])->middleware("throttle:api")->name('register');

// Route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// Route to maintain the session
// Updated to include validation and error handling as per the requirement
Route::middleware('auth:sanctum')->post('/session/maintain', [SessionController::class, 'maintainSession']);

// Route to update user profile
Route::middleware('auth:sanctum')->put('/users/{user}/profile', [UserController::class, 'updateUserProfile'])
    ->name('users.update.profile');

// Route to update shop information
// The new code has a different endpoint for updating shop information, we need to update the route to match the requirement.
// We will also add the necessary validation and authorization within the ShopController's updateShop method.
Route::middleware('auth:sanctum')->match(['put', 'patch'], '/shops/{id}', [ShopController::class, 'updateShop'])
    ->name('shops.update');

// Route for user logout
// The new code has a different endpoint for logout, we need to update the route to match the requirement.
Route::middleware('auth:sanctum')->post('/auth/logout', [LogoutController::class, 'logout'])
    ->name('logout')
    ->middleware('throttle:api');
