<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\HairStylistRequestController; // Import the HairStylistRequestController

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
Route::post('/users/reset-password', [ResetPasswordController::class, 'resetPassword']);

// New route for user registration with throttle middleware
Route::post('/users/register', [RegisterController::class, 'register'])->middleware('throttle:api');

// New route for cancelling a hair stylist request
Route::delete('/hair_stylist_requests/{id}', [HairStylistRequestController::class, 'destroy'])
    ->middleware('auth:sanctum')
    ->where('id', '[0-9]+');
