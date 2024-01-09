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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("/users/reset-password", [ResetPasswordController::class, "resetPassword"]);

Route::post("/api/login", [LoginController::class, "login"]);

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

Route::middleware('auth:sanctum')->post('/stylist-request/create', [StylistRequestController::class, 'createStylistRequest']);

Route::middleware('auth:sanctum')->put('/stylist-request/update/{id}', [StylistRequestController::class, 'update'])
    ->where('id', '[0-9]+')
    ->name('stylist-request.update');

Route::middleware('auth:sanctum')->post('/stylist-request/cancel/{id}', [StylistRequestController::class, 'cancelStylistRequest'])
    ->where('id', '[0-9]+');

Route::middleware('auth:sanctum')->post('/api/hair-stylist-requests/create', [HairStylistRequestController::class, 'createHairStylistRequest'])->name('hair-stylist-requests.create');
Route::middleware('auth:sanctum')->post('/cancel-login', [AuthController::class, 'cancelLogin'])->name('auth.cancel-login');

Route::post('/api/password_reset_requests', [PasswordResetRequestController::class, 'store'])->middleware('throttle:api');

// The new route for creating hair stylist requests is added as per the requirement
Route::middleware('auth:sanctum')->post('/api/hair-stylist-request/create', [HairStylistRequestController::class, 'createHairStylistRequest']);
