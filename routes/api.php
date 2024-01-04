<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\Auth\ForgotPasswordController; // Import the ForgotPasswordController
use App\Http\Controllers\UserController; // Import the UserController
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

// New route for resetting the user's password
Route::post("/users/reset-password", [ResetPasswordController::class, "resetPassword"]);

// New route for user registration with throttle middleware
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

// New route to handle the DELETE request for the endpoint `/api/requests/{request_id}/images/{image_id}`
Route::middleware('auth:sanctum')->delete('/requests/{request_id}/images/{image_id}', [UserController::class, 'deleteRequestImage']);

// New route for requesting a password reset
Route::post('/users/password_reset_request', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ], [
        'email.required' => 'Email is required.',
        'email.email' => 'Invalid email format.',
        'email.exists' => 'Email not found.',
    ]);

    if ($validator->fails()) {
        $errors = $validator->errors();
        $firstError = $errors->first();
        $statusCode = $firstError === 'Email not found.' ? 404 : 400;
        return response()->json(['message' => $firstError], $statusCode);
    }

    // Assuming the ForgotPasswordController's requestPasswordReset method handles the business logic
    // and returns the appropriate response.
    $forgotPasswordController = new ForgotPasswordController();
    return $forgotPasswordController->requestPasswordReset($request);
});

// New route for sending a password reset link email
Route::post('/auth/send-reset-link-email', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ], [
        'email.required' => 'Please enter a valid email address.',
        'email.email' => 'Please enter a valid email address.',
        'email.exists' => 'The email address does not exist in our records.',
    ]);

    if ($validator->fails()) {
        $errors = $validator->errors();
        $firstError = $errors->first();
        $statusCode = 422; // Unprocessable Entity for validation errors
        if ($firstError === 'The email address does not exist in our records.') {
            $statusCode = 404; // Not Found for non-existing email
        }
        return response()->json(['message' => $firstError], $statusCode);
    }

    // Assuming the ForgotPasswordController's sendResetLinkEmail method handles the business logic
    // and returns the appropriate response.
    $forgotPasswordController = new ForgotPasswordController();
    return $forgotPasswordController->sendResetLinkEmail($request);
})->middleware('api');
