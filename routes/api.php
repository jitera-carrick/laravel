<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController; // Import the AuthController
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\Auth\ForgotPasswordController; // Import the ForgotPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\SessionController; // Import the SessionController
use App\Http\Controllers\RequestImageController; // Import the RequestImageController
use App\Http\Controllers\HairStylistRequestController; // Import the HairStylistRequestController
use App\Http\Controllers\LogoutController; // Import the LogoutController
use App\Http\Controllers\EmailVerificationController; // Import the EmailVerificationController

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
// The new code has a different URI for password reset, we need to keep both URIs
Route::post("/users/reset-password", [ResetPasswordController::class, "resetPassword"]);
Route::post("/api/auth/password/reset", [ResetPasswordController::class, "resetPassword"])->middleware('throttle:api');

// Route for user login
// The new code has a different URI for login, we need to keep both URIs
Route::post("/login", [AuthController::class, "login"]);
Route::post("/api/auth/login", [AuthController::class, "login"]);

// Route for user registration with throttle middleware
// The new code has a different URI for registration, we need to keep both URIs
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");
Route::post("/api/auth/register", [RegisterController::class, "register"])->middleware("throttle:api");

// Route to maintain the session
Route::post('/session/maintain', [SessionController::class, 'maintainSession']);

// New POST route for creating hair stylist requests
Route::middleware('auth:sanctum')->post('/hair-stylist-requests', [HairStylistRequestController::class, 'createHairStylistRequest']);

// Route to handle the DELETE request for the endpoint `/api/user/hair-stylist-request/image`
Route::middleware('auth:sanctum')->delete('/user/hair-stylist-request/image', [RequestImageController::class, 'deleteRequestImage']);

// Existing route to handle the DELETE request for the endpoint `/api/requests/images/{request_image_id}`
Route::middleware('auth:sanctum')->delete('/requests/images/{request_image_id}', [RequestImageController::class, 'deleteRequestImage']);

// New POST route for user logout
Route::middleware('auth:sanctum')->post('/logout', [LogoutController::class, 'logout']);

// New POST route for sending a password reset link email
Route::post('/auth/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);

// Route for email verification
// The new code uses GET and the existing code uses POST, we need to support both methods
Route::get('/email/verify/{token}', [EmailVerificationController::class, 'verifyEmail'])->name('email.verify');
Route::post('/api/auth/email/verify/{token}', [EmailVerificationController::class, 'verify'])->name('api.auth.email.verify');
