<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\Auth\ForgotPasswordController; // Import the ForgotPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\RequestController; // Import the RequestController
use App\Http\Controllers\RequestImageController; // Import the RequestImageController
use App\Http\Controllers\Auth\VerifyEmailController; // Import the VerifyEmailController
use App\Http\Controllers\Auth\LoginController; // Import the LoginController

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

// Route to handle the DELETE request for the endpoint `/api/hair-stylist-requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/hair-stylist-requests/{request_id}/images/{image_id}', [RequestImageController::class, 'deleteHairStylistRequestImage']);

// Route to handle the PATCH request for the endpoint `/api/hair-stylist-requests/{id}`
Route::middleware('auth:sanctum')->patch('/hair-stylist-requests/{id}', [RequestController::class, 'updateHairStylistRequest']);

// New POST route for the endpoint "/api/hair-stylist-requests"
// This route is used to create a new hair stylist request by an authenticated user
Route::middleware('auth:sanctum')->post('/hair-stylist-requests', [RequestController::class, 'createHairStylistRequest'])->name('hair-stylist-requests.create');

// New route for sending the password reset link email
Route::post('/auth/send-reset-link-email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:api');

// New route for verifying the user's email
Route::post('/auth/verify/{token}', [VerifyEmailController::class, 'verifyEmail']);

// New POST route for user login
Route::post('/auth/login', [LoginController::class, 'login'])->middleware('throttle:api');
