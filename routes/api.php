<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\StylistRequestController;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Password Reset Routes
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [ResetPasswordController::class, 'reset']);

// Register Route
Route::post('/register', [RegisterController::class, 'register'])->middleware('api');

// Login Route
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');

// Cancel Login Route
Route::post('/login/cancel', [LoginController::class, 'cancelLogin'])->middleware('api');

// Password Reset Token Validation Route
Route::get('/users/password_reset/validate_token', [ResetPasswordController::class, 'validateToken'])->middleware('api');

// Set New Password Route
Route::put('/users/password_reset/set_new', [ResetPasswordController::class, 'setNewPassword'])->middleware('api');

// Add new route for password reset request
Route::post('/password/reset/request', [ForgotPasswordController::class, 'requestPasswordReset'])->middleware('api');

// Add a new route to handle the PUT request for verifying email and setting the password
Route::put('/verify_email_set_password', [RegisterController::class, 'verifyEmailSetPassword'])->middleware('api');

// Add a new POST route `/api/send_registration_email` that maps to the `sendRegistrationEmail` method in the RegisterController.
Route::post('/send_registration_email', [RegisterController::class, 'sendRegistrationEmail'])->middleware('api');

// Add a new route to handle the POST request for creating a stylist request
Route::post('/stylist_requests', [StylistRequestController::class, 'createStylistRequest'])->middleware('auth:sanctum');

// New route for handling failed login attempts
Route::post('/login/failed', [LoginController::class, 'logFailedLogin'])->middleware('api');
