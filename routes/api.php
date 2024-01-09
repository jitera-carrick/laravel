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

Route::post("/login", [AuthController::class, "login"]);

Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

Route::post('/session/maintain', [SessionController::class, 'maintainSession']);

// The following route is from the existing code but was updated in the new code to use POST instead of PUT.
// Since the new code specifies a POST route for creating stylist requests, we will keep the new code's version.
// This is a decision based on the assumption that the new code's change was intentional and should override the existing code.
Route::middleware('auth:sanctum')->post('/stylist-request/create', [StylistRequestController::class, 'createStylistRequest']);

// The existing PUT route for updating stylist requests is kept to maintain backward compatibility.
// Clients using the old endpoint will still be able to update stylist requests.
Route::middleware('auth:sanctum')->put('/stylist-request/update/{id}', [StylistRequestController::class, 'update'])
    ->where('id', '[0-9]+')
    ->name('stylist-request.update');

// The POST route for canceling stylist requests from the new code is kept.
// This change is assumed to be intentional and should override the existing code.
Route::middleware('auth:sanctum')->post('/stylist-request/cancel/{id}', [StylistRequestController::class, 'cancelStylistRequest'])
    ->where('id', '[0-9]+');

// New route to handle POST request to /api/hair_stylist_requests with validation for user_id
Route::middleware('auth:sanctum')->post('/hair_stylist_requests', function (Request $request) {
    $validatedData = $request->validate([
        'user_id' => 'required|exists:users,id',
    ]);

    // Assuming the existence of a method in HairStylistRequestController to handle the request creation
    return app(HairStylistRequestController::class)->createHairStylistRequest($request);
});

// The route for canceling the login process from the existing code is kept.
Route::middleware('auth:sanctum')->post('/cancel-login', [AuthController::class, 'cancelLogin'])->name('auth.cancel-login');

// The route for creating hair stylist requests from the existing code is kept.
Route::middleware('auth:sanctum')->post('/hair-stylist-requests/create', [HairStylistRequestController::class, 'createHairStylistRequest']);

// The route for deleting request images from the existing code is kept.
Route::middleware('auth:sanctum')->delete('/requests/images/{request_image_id}', [RequestImageController::class, 'deleteRequestImage']);

// The route for updating hair stylist requests from the existing code is kept.
Route::middleware('auth:sanctum')->match(['put', 'patch'], '/hair-stylist-requests/{id}', [HairStylistRequestController::class, 'updateHairStylistRequest']);

// The route for creating stylist requests from the existing code is kept.
Route::middleware('auth:sanctum')->post('/stylist-requests', [StylistRequestController::class, 'createStylistRequest']);

// The route for deleting hair stylist request images from the existing code is kept.
Route::middleware('auth:sanctum')->delete('/user/hair-stylist-request/image', [RequestImageController::class, 'deleteRequestImage']);

// The route for canceling stylist requests from the existing code is kept.
Route::middleware('auth:sanctum')->delete('/stylist-requests/{id}', [StylistRequestController::class, 'cancelStylistRequest'])
    ->where('id', '[0-9]+')
    ->name('stylist-requests.cancel');

// The new route for canceling the login process as per the requirement from the existing code is kept.
Route::post('/api/login/cancel', [SessionController::class, 'cancelLoginProcess']);
