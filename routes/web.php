<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{token}', [App\Http\Controllers\Auth\VerifyEmailController::class, 'verify'])
    ->name('verification.verify');

# Note: The controller method should handle the token verification logic as per the guidelines provided.