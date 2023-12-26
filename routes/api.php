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

// New route for registering user account (merged from new code)
// This route has been modified to use the existing RegisterController instead of inline closure.
Route::post('/register', [RegisterController::class, 'register'])->middleware('api');

// New route for login (merged from new code)
// This route has been modified to use the existing LoginController instead of inline closure.
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');

// New route for canceling login (merged from new code)
// This route has been modified to use the existing LoginController instead of inline closure.
Route::post('/login/cancel', [LoginController::class, 'cancelLogin'])->middleware('api');

// Password Reset Error Handling Route (merged from new code)
// This route has been added as it does not conflict with existing routes.
Route::post('/users/password_reset/error', function (Request $request) {
    // Validate the input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'errors' => [
                [
                    'field' => 'email',
                    'message' => 'Email address is required.'
                ]
            ]
        ], 400);
    }

    if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
        return response()->json([
            'status' => 400,
            'errors' => [
                [
                    'field' => 'email',
                    'message' => 'Invalid email address format.'
                ]
            ]
        ], 400);
    }

    // If the email is valid, but other errors need to be handled, you can add additional logic here.
    // For the purpose of this requirement, we will return a generic error message.
    return response()->json([
        'status' => 500,
        'errors' => [
            [
                'field' => 'email',
                'message' => 'An unexpected error occurred.'
            ]
        ]
    ], 500);
})->middleware('api');
