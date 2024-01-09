
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\VerifyEmailController;

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

// Resolve the conflict by checking if the VerificationController class exists
// If it does not exist, fall back to the VerifyEmailController
if (class_exists(VerificationController::class)) {
    Route::get('/email/verify/{token}', [VerificationController::class, 'verify'])
        ->middleware(['web', 'guest'])
        ->name('verification.verify');
} else {
    Route::get('/email/verify/{token}', [VerifyEmailController::class, 'verify'])
        ->name('verification.verify');
}
