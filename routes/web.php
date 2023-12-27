<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Controller;

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

// Update the existing route '/cancel-login' to utilize the `cancelLogin` method from the `Controller` class.
// The cancelLogin method should be created in the Controller to handle the cancellation process.
Route::get('/cancel-login', [Controller::class, 'cancelLogin'])->name('cancel-login');
