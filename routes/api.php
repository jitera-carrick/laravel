<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Existing route to get user details
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// New route for updating shop details
Route::middleware('auth:sanctum')->match(['put', 'patch'], '/shop/update', 'ShopController@updateShop');

// New route for email verification
Route::get('/users/verify/{token}', 'Auth\VerificationController@verifyEmail')->name('verification.verify');
