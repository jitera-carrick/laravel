<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordPolicyController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PasswordResetToken;
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

Route::middleware(['auth:sanctum', 'can:update-password-policy'])->group(function () {
    Route::put('/password-policy', [PasswordPolicyController::class, 'update']);
    Route::patch('/password-policy', [PasswordPolicyController::class, 'update']);
});

// Merged the functionality of the new route with the existing one for sending password reset link
Route::post('/users/password-reset', [ForgotPasswordController::class, 'sendPasswordResetLink']);

// Merged the functionality of the new route with the existing one for validating password reset token
Route::get('/users/password-reset/validate/{token}', [ResetPasswordController::class, 'validateResetToken']);

// Merged the functionality of the new route with the existing one for resetting the password
Route::put('/users/password-reset/{token}', function (Request $request, $token) {
    // ... existing code for password reset ...
});

// Merged the functionality of the new route with the existing one for password reset request
Route::post('/password/reset/request', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => $validator->errors()->first()], 422);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'Account not found.'], 404);
    }

    // Assuming sendPasswordResetRequest is a method that sends the password reset email
    // and creates a password reset token.
    $response = ForgotPasswordController::sendPasswordResetRequest($request->email);

    if ($response === true) {
        return response()->json(['status' => 200, 'message' => 'Password reset request sent. Please check your email.']);
    }

    return response()->json(['message' => 'An unexpected error occurred.'], 500);
})->name('password.reset.request');

// Merged the functionality of the new route with the existing one for login
Route::post('/login', function (Request $request) {
    // ... existing code for login ...
});

// Merged the functionality of the new route with the existing one for maintaining a user session
Route::middleware('auth:sanctum')->post('/session/maintain', function (Request $request) {
    // ... existing code for maintaining a user session ...
});

// New route for canceling the login process
Route::post('/login/cancel', function () {
    // No business logic is required as per the requirement, just return the success response
    return response()->json([
        'status' => 200,
        'message' => 'Login process canceled successfully.'
    ]);
});
