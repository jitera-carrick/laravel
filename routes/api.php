<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\RequestController; // Import the RequestController if not already imported

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
Route::post("/users/reset-password", [ResetPasswordController::class, 'resetPassword']);

// New route for user registration with throttle middleware
// Updated: Changed the URI to match the existing code and added validation rules as per the requirement
Route::post("/users/register", [RegisterController::class, "register"])
    ->middleware("throttle:api")
    ->name('users.register'); // Added the name for the route

// New route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// New route for PATCH request to update a hair stylist request
// This route is added within the auth:sanctum middleware group
Route::middleware(['auth:sanctum'])->group(function () {
    Route::patch('/hair-stylist-requests/{id}', [RequestController::class, 'update'])->name('hair-stylist-requests.update');
});
