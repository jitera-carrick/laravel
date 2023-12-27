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

// Add a new route for cancelling the login process
Route::get('/cancel-login', function () {
    // Since no database actions are required, we directly return a response.
    return response()->json(['message' => 'Login cancelled. No changes were made.']);
})->name('cancel-login');
