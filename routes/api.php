<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\Auth\ForgotPasswordController; // Import the ForgotPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\Auth\AuthController; // Import the AuthController
use App\Http\Controllers\Auth\VerifyEmailController; // Import the VerifyEmailController
use Illuminate\Support\Facades\Validator; // Import the Validator facade

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

// Existing route for getting the authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route for resetting the user's password
Route::post("/users/reset-password", [ResetPasswordController::class, "resetPassword"]);

// Route for user registration with throttle middleware
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

// Route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// Route for validating the reset password token
Route::post('/api/auth/validate-reset-token', [ResetPasswordController::class, 'validateResetToken']);

// Route for user login
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:api');

// Send a reset link to the user's email address
Route::post('/auth/send-reset-link-email', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ], [
        'email.exists' => 'Email address not found.',
        'email.email' => 'Invalid email address format.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => $validator->errors()->first() === 'Email address not found.' ? 400 : 422,
            'message' => $validator->errors()->first(),
        ], $validator->errors()->first() === 'Email address not found.' ? 400 : 422);
    }

    // Assuming the ForgotPasswordController has a method sendResetLinkEmail that handles sending the email
    $response = app(ForgotPasswordController::class)->sendResetLinkEmail($request);

    return $response;
})->middleware('throttle:api');

// New route to verify the user's email
Route::middleware('auth:sanctum')->post('/auth/verify/{token}', [VerifyEmailController::class, 'verify'])->where('token', '[a-zA-Z0-9]+');
