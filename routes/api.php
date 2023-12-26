<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController; // Import the LoginController
use App\Http\Controllers\SessionController; // Import SessionController
use App\Http\Controllers\VerificationController; // Add this line to use VerificationController

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

// Existing routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// New route for password reset
Route::post('/password/email', function (Request $request) {
    // ... existing password reset code ...
    // The new password reset logic will be merged here
    // ... existing code ...
})->middleware('throttle:6,1');

// New route for email verification
Route::post('/email/verify', function (Request $request) {
    // ... existing email verification code ...
    // The new email verification logic will be merged here
    // ... existing code ...
})->middleware('throttle:6,1');

// Updated route for user registration to meet the requirements
Route::post('/users/register', function (Request $request) {
    // ... existing user registration code ...
    // The new user registration logic will be merged here
    // ... existing code ...
})->middleware('throttle:6,1');

// New route for user login
Route::post('/users/login', function (Request $request) {
    // ... existing user login code ...
    // The new user login logic will be merged here
    // ... existing code ...
});

// New route for maintaining session preferences
Route::put('/maintain_session', function (Request $request) {
    // ... existing code for maintaining session preferences ...
    // The new maintain session preferences logic will be merged here
    // ... existing code ...
});

// New route for updating user profile
Route::middleware('auth:sanctum')->put('/user/profile', function (Request $request) {
    // ... existing update user profile code ...
    // The new update user profile logic will be merged here
    // ... existing code ...
});

// New route for user logout
Route::delete('/users/logout', function (Request $request) {
    // ... existing user logout code ...
    // The new user logout logic will be merged here
    // ... existing code ...
})->middleware('auth:sanctum');

// New route for handling login failure
Route::post('/login_failure', function (Request $request) {
    // ... existing login failure handling code ...
    // The new login failure handling logic will be merged here
    // ... existing login failure handling code ...
});

// Remove the old registration route as it is now redundant
// Route::post('/user/register', function (Request $request) {
//     ...
// });
