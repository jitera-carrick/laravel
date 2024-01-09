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
use App\Http\Controllers\StylistRequestController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordResetRequestController;
use App\Http\Controllers\ForgotPasswordController;
use App\Models\User;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("/users/reset-password", [ResetPasswordController::class, "resetPassword"]);

// Updated login route to include validation as per the requirement
// Merged the login route from both new and existing code, keeping the throttle middleware
Route::post("/api/login", [LoginController::class, "login"])->middleware('throttle:api');

Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

Route::post('/session/maintain', [SessionController::class, 'maintainSession']);

Route::middleware('auth:sanctum')->post('/stylist-requests', [StylistRequestController::class, 'createStylistRequest']);

Route::middleware('auth:sanctum')->post('/hair-stylist-requests/store', [HairStylistRequestController::class, 'store'])->name('hair-stylist-requests.store');

Route::middleware('auth:sanctum')->delete('/user/hair-stylist-request/image', [RequestImageController::class, 'deleteRequestImage']);

Route::middleware('auth:sanctum')->delete('/stylist-requests/{id}', [StylistRequestController::class, 'cancelStylistRequest'])
    ->where('id', '[0-9]+')
    ->name('stylist-requests.cancel');

Route::middleware('auth:sanctum')->delete('/requests/images/{request_image_id}', [RequestImageController::class, 'deleteRequestImage']);

Route::middleware('auth:sanctum')->match(['put', 'patch'], '/hair-stylist-requests/{id}', [HairStylistRequestController::class, 'updateHairStylistRequest']);

// Removed duplicate route for creating stylist requests
Route::middleware('auth:sanctum')->put('/stylist-request/update/{id}', [StylistRequestController::class, 'update'])
    ->where('id', '[0-9]+')
    ->name('stylist-request.update');

Route::middleware('auth:sanctum')->post('/stylist-request/cancel/{id}', [StylistRequestController::class, 'cancelStylistRequest'])
    ->where('id', '[0-9]+');

// Consolidated the cancel-login routes to avoid duplication and conflict
// Kept the route from the existing code as it seems to be the most updated one
Route::middleware('auth:sanctum')->post('/api/cancel-login', [LoginController::class, 'cancelLogin'])->name('auth.cancel-login');

// Merged the password reset request routes to use the ForgotPasswordController as per the new code
// Kept the middleware for validation from the existing code
Route::post('/api/password_reset_requests', [ForgotPasswordController::class, 'createPasswordResetRequest'])
    ->middleware('throttle:api')
    ->middleware('validate.password_reset_request'); // Added middleware for validation

// The new route for creating hair stylist requests is added as per the requirement
Route::middleware('auth:sanctum')->post('/api/hair-stylist-request/create', [HairStylistRequestController::class, 'createHairStylistRequest']);

// Handle login failure
Route::get('/api/login/failure', [LoginController::class, 'handleLoginFailure'])->middleware('throttle:api');
