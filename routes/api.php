<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Validator;

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

// Route for getting the authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route for resetting the user's password
Route::post("/users/reset-password", [ResetPasswordController::class, "resetPassword"]);

// Route for user registration with throttle middleware
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

// Route to handle the DELETE request for the endpoint `/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// Route for validating the reset password token
Route::post('/api/auth/validate-reset-token', [ResetPasswordController::class, 'validateResetToken']);

// Route for user login
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:api');

// Route for sending reset link email with throttle middleware
Route::post('/auth/send-reset-link-email', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ], [
        'email.exists' => 'Email address not found.',
        'email.email' => 'Invalid email address format.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => $validator->errors()->first() === 'Email address not found.' ? 400 : 422,
            'message' => $validator->errors()->first(),
        ], $validator->errors()->first() === 'Email address not found.' ? 400 : 422);
    }

    $response = app(ForgotPasswordController::class)->sendResetLinkEmail($request);

    return $response;
})->middleware('throttle:api');

// Route for deleting a hair stylist request image with auth and permission middleware
Route::delete('/hair-stylist-request/{request_id}/image/{image_id}', [RequestController::class, 'deleteHairStylistRequestImage'])
    ->middleware('auth:sanctum')
    ->where('request_id', '[0-9]+')
    ->where('image_id', '[0-9]+')
    ->middleware(['can:delete,request_id']);

// New route for creating a hair stylist request
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/hair-stylist-request/create', [RequestController::class, 'createHairStylistRequest']);
});
