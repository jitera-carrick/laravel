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

// Updated the login route to match the requirement
Route::post("/api/login", [LoginController::class, "login"]);

// New route for handling login failure from existing code
Route::post("/api/users/login_failure", [LoginController::class, "handleLoginFailure"]);

Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

Route::post('/session/maintain', [SessionController::class, 'maintainSession']);

// Removed the duplicate route for creating a stylist request
// Kept the route from the new code as it is more recent
Route::middleware('auth:sanctum')->post('/stylist-requests', [StylistRequestController::class, 'createStylistRequest']);

// Updated the route for creating a hair stylist request to match the requirement
// The route from the new code is kept and renamed to match the existing pattern
Route::middleware('auth:sanctum')->post('/api/hair_stylist_requests', [HairStylistRequestController::class, 'sendRequestToStylist']);

Route::middleware('auth:sanctum')->delete('/user/hair-stylist-request/image', [RequestImageController::class, 'deleteRequestImage']);

Route::middleware('auth:sanctum')->delete('/stylist-requests/{id}', [StylistRequestController::class, 'cancelStylistRequest'])
    ->where('id', '[0-9]+')
    ->name('stylist-requests.cancel');

Route::middleware('auth:sanctum')->delete('/requests/images/{request_image_id}', [RequestImageController::class, 'deleteRequestImage']);

Route::middleware('auth:sanctum')->match(['put', 'patch'], '/hair-stylist-requests/{id}', [HairStylistRequestController::class, 'updateHairStylistRequest']);

Route::middleware('auth:sanctum')->put('/stylist-request/update/{id}', [StylistRequestController::class, 'update'])
    ->where('id', '[0-9]+')
    ->name('stylist-request.update');

Route::middleware('auth:sanctum')->post('/stylist-request/cancel/{id}', [StylistRequestController::class, 'cancelStylistRequest'])
    ->where('id', '[0-9]+');

// The route for canceling the login process from the existing code is kept.
Route::middleware('auth:sanctum')->post('/cancel-login', [AuthController::class, 'cancelLogin'])->name('auth.cancel-login');

// Added new route for password reset request as per the requirement
Route::post('/api/password_reset_requests', [PasswordResetRequestController::class, 'store'])->middleware('throttle:api');
