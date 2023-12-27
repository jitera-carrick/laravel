<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;
use App\Http\Controllers\LoginController; // Import the LoginController

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

// Update the existing route '/cancel-login' to utilize the `cancelLogin` method from the `LoginController` class.
// The cancelLogin method should be created in the LoginController to handle the cancellation process.
Route::get('/cancel-login', [LoginController::class, 'cancelLogin'])->name('cancel-login');
