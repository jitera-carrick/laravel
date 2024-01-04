<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController; // Import the RegisterController
use App\Http\Controllers\Auth\ResetPasswordController; // Import the ResetPasswordController
use App\Http\Controllers\Auth\ForgotPasswordController; // Import the ForgotPasswordController
use App\Http\Controllers\UserController; // Import the UserController
use App\Http\Controllers\Auth\VerifyEmailController; // Import the VerifyEmailController
use App\Http\Controllers\Auth\AuthController; // Import the AuthController
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

// Route for the logout endpoint
Route::middleware('auth:sanctum')->post('/auth/logout', [AuthController::class, 'logout']);

// Route for verifying the user's email
Route::post('/auth/verify-email/{token}', [VerifyEmailController::class, 'verifyEmail'])->middleware('throttle:6,1')->name('verification.verify');

// Route for validating the reset password token
Route::post('/auth/validate-reset-token', [ResetPasswordController::class, 'validateResetToken']);

// Route for sending the password reset link email
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
        if ($errors->has('email')) {
            return response()->json([
                'status' => $errors->first('email') === 'The email address does not exist in our records.' ? 404 : 422,
                'message' => $errors->first('email'),
            ], $errors->first('email') === 'The email address does not exist in our records.' ? 404 : 422);
        }
        return response()->json(['status' => 400, 'message' => 'Bad Request'], 400);
    }

    // Assuming ForgotPasswordController has a method sendResetLinkEmail that handles sending the email
    $response = app(ForgotPasswordController::class)->sendResetLinkEmail($request);

    return $response;
});
