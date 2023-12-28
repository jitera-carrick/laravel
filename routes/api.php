<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController; // Import UserController
use App\Http\Controllers\ShopController; // Import ShopController

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// New route for updating shop details
Route::middleware('auth:sanctum')->match(['put', 'patch'], '/shop/update', [ShopController::class, 'updateShop']);

// New route for updating user profile
Route::middleware('auth:sanctum')->put('/users/profile', [UserController::class, 'updateProfile']);
