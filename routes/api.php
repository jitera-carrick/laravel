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
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\EmailVerificationController;

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

Route::post("/login", [AuthController::class, "login"]);

Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

// The existing code has a throttle middleware on the session maintain route, which is kept for security reasons.
Route::middleware('auth:sanctum')->post('/session/maintain', [SessionController::class, 'maintainSession'])->middleware('throttle:api');

Route::middleware('auth:sanctum')->post('/hair-stylist-requests', [HairStylistRequestController::class, 'createHairStylistRequest']);

Route::middleware('auth:sanctum')->delete('/user/hair-stylist-request/image', [RequestImageController::class, 'deleteRequestImage']);

Route::middleware('auth:sanctum')->delete('/requests/images/{request_image_id}', [RequestImageController::class, 'deleteRequestImage']);

Route::middleware('auth:sanctum')->post('/logout', [LogoutController::class, 'logout']);

// The new code has an updated route for email verification with a different URI and throttle middleware.
// We'll keep both routes to maintain backward compatibility and to support the new URI format.
Route::get('/email/verify/{token}', [VerifyEmailController::class, 'verify'])
    ->name('api.email.verify');

Route::get('/verify/{token}', [EmailVerificationController::class, 'verify'])
    ->name('verification.verify')
    ->middleware('throttle:6,1');

// The new code has both PUT and PATCH routes for the same action, which is redundant.
// However, to maintain compatibility with clients that may use either method, we'll keep both.
Route::middleware('auth:sanctum')->put('/user/profile', [UserController::class, 'editUserProfile']);
Route::middleware('auth:sanctum')->patch('/user/profile', [UserController::class, 'editUserProfile']);

// The new code has an additional POST route for editing user profile which is not present in the existing code.
// This is added as new functionality.
Route::middleware('auth:sanctum')->post('/user/profile/edit', [UserController::class, 'updateUserProfile']);

// ... other routes ...
