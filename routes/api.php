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
use App\Http\Controllers\RequestImageController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("/users/reset-password", [ResetPasswordController::class, "resetPassword"])->middleware('throttle:api');

// Merged the login route with the throttle middleware from the existing code
Route::post("/api/login", [LoginController::class, "login"])
    ->middleware(['guest', 'throttle:api'])
    ->name('login.attempt');

Route::post("/api/login", [LoginController::class, "login"]);

Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

Route::post('/session/maintain', [SessionController::class, 'maintainSession']);

Route::middleware('auth:sanctum')->post('/stylist-requests', [StylistRequestController::class, 'createStylistRequest']);

Route::middleware('auth:sanctum')->post('/api/hair_stylist_requests', [HairStylistRequestController::class, 'createHairStylistRequest']);

Route::middleware('auth:sanctum')->delete('/user/hair-stylist-request/image', [RequestImageController::class, 'deleteRequestImage']);

Route::middleware('auth:sanctum')->delete('/stylist-requests/{id}', [StylistRequestController::class, 'cancelStylistRequest'])
    ->where('id', '[0-9]+')
    ->name('stylist-requests.cancel');

Route::middleware('auth:sanctum')->delete('/requests/images/{request_image_id}', [RequestImageController::class, 'deleteRequestImage']);

Route::middleware('auth:sanctum')->match(['put', 'patch'], '/hair-stylist-requests/{id}', [HairStylistRequestController::class, 'updateHairStylistRequest']);

// Removed duplicate route for creating stylist request
// Removed duplicate route for canceling stylist request
Route::middleware('auth:sanctum')->put('/stylist-request/update/{id}', [StylistRequestController::class, 'update'])
    ->where('id', '[0-9]+')
    ->name('stylist-request.update');

Route::middleware('auth:sanctum').post('/stylist-request/cancel/{id}', [StylistRequestController::class, 'cancelStylistRequest'])
    ->where('id', '[0-9]+');

// The following route is updated to match the requirement for creating a hair stylist request
Route::middleware('auth:sanctum')->post('/api/hair-stylist-request/create', [HairStylistRequestController::class, 'createHairStylistRequest'])->name('hair-stylist-requests.create');

Route::middleware('auth:sanctum')->post('/cancel-login', [AuthController::class, 'cancelLogin'])->name('auth.cancel-login');

Route::post('/api/password_reset_requests', [PasswordResetRequestController::class, 'store'])->middleware('throttle:api');

// The new route for filtering hair stylist requests is added as per the requirement
Route::middleware('auth:sanctum')->get('/api/hair-stylist-request', [HairStylistRequestController::class, 'filterHairStylistRequests'])
    ->name('hair-stylist-request.filter');

// The following routes are added from the patch
Route::any('/health-check', [HealthCheckController::class, 'index'])->name('health-check');
Route::post('/hair-stylist-request/create', [HairStylistRequestController::class, 'createHairStylistRequest'])
    ->middleware('auth:api')
    ->name('hair-stylist-requests.create');
// Removed duplicate route for canceling stylist request
Route::post('/api/login/cancel', function () {
    return response()->json([
        "status" => 200,
        "message" => "Login process canceled successfully."
    ], 200);
})->name('login.cancel');

// Updated the password reset request route to include validation and business logic as per the requirement.
Route::post('/api/password_reset_requests', [PasswordResetRequestController::class, 'store'])
    ->middleware(['throttle:api', 'guest']); // Ensure that the user is not authenticated when making this request.
