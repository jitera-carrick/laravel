<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\HairStylistRequestController;
use App\Http\Controllers\StylistRequestController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetRequestController;
use App\Http\Controllers\RequestImageController; // Added missing use statement for RequestImageController

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Consolidated the two reset-password routes into one, using the throttle middleware.
Route::post("/users/reset-password", [ResetPasswordController::class, "resetPassword"])->middleware('throttle:api');

// The login route uses the 'guest' middleware and has a named route 'login.attempt'.
// Added the login failure route from the existing code.
Route::post("/api/login", [LoginController::class, "login"])->middleware('guest')->name('login.attempt');
Route::post('/api/login/failure', [LoginController::class, 'handleLoginFailure']);

Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

Route::post('/session/maintain', [SessionController::class, 'maintainSession']);

Route::middleware('auth:sanctum')->post('/stylist-requests', [StylistRequestController::class, 'createStylistRequest']);

// The route for storing hair stylist requests has been updated to match the requirement.
Route::middleware('auth:sanctum')->post('/api/hair_stylist_requests', [HairStylistRequestController::class, 'createHairStylistRequest']);

Route::middleware('auth:sanctum')->delete('/user/hair-stylist-request/image', [RequestImageController::class, 'deleteRequestImage']);

Route::middleware('auth:sanctum')->delete('/stylist-requests/{id}', [StylistRequestController::class, 'cancelStylistRequest'])
    ->where('id', '[0-9]+')
    ->name('stylist-requests.cancel');

Route::middleware('auth:sanctum')->delete('/requests/images/{request_image_id}', [RequestImageController::class, 'deleteRequestImage']);

Route::middleware('auth:sanctum')->match(['put', 'patch'], '/hair-stylist-requests/{id}', [HairStylistRequestController::class, 'updateHairStylistRequest']);

// Removed duplicate route for creating stylist requests, keeping the one with the correct URI.
Route::middleware('auth:sanctum')->post('/stylist-request/create', [StylistRequestController::class, 'createStylistRequest']);

Route::middleware('auth:sanctum')->put('/stylist-request/update/{id}', [StylistRequestController::class, 'update'])
    ->where('id', '[0-9]+')
    ->name('stylist-request.update');

// Removed duplicate route for canceling stylist requests, keeping the one with the correct URI and method.
Route::middleware('auth:sanctum')->post('/stylist-request/cancel/{id}', [StylistRequestController::class, 'cancelStylistRequest'])
    ->where('id', '[0-9]+');

// The route for canceling the login process has been updated to match the requirement.
Route::post('/api/login/cancel', function () {
    return response()->json([
        "status" => 200,
        "message" => "Login process cancelled successfully."
    ], 200);
})->name('login.cancel');

// Removed duplicate route for password reset requests, keeping the one with the correct URI.
Route::post('/api/password_reset_requests', [PasswordResetRequestController::class, 'store'])->middleware('throttle:api');
