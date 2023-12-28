<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShopController;

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
// Updated to use the 'auth:api' middleware as per the guideline and pointing to the 'update' method
Route::middleware('auth:api')->put('/users/shop', [ShopController::class, 'update']);

// Keeping the old route for backward compatibility or other purposes
Route::middleware('auth:sanctum')->match(['put', 'patch'], '/shop/update', 'ShopController@updateShop');
