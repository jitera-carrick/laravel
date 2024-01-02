<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\RequestController; // Import the RequestController

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

// New route for resetting the user's password
Route::post('/users/reset-password', [ResetPasswordController::class, "resetPassword"]);

// New route for user registration with throttle middleware
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

// New route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// New route for creating a request, protected by auth:sanctum middleware
Route::middleware('auth:sanctum')->post('/requests', [RequestController::class, 'create']);

// New route to handle the PUT request for the endpoint `/api/hair_stylist_requests/{id}`
Route::middleware('auth:sanctum')->put('/hair_stylist_requests/{id}', [RequestController::class, 'updateHairStylistRequest']);

// New route to handle the DELETE request for the endpoint `/api/hair_stylist_requests/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/hair_stylist_requests/images/{image_id}', [RequestController::class, 'deleteHairStylistRequestImage']);
