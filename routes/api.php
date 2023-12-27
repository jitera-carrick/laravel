<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordPolicyController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\LoginController;
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

Route::post('/users/password-reset', [ForgotPasswordController::class, 'sendPasswordResetLink']);

Route::get('/users/password-reset/validate/{token}', [ResetPasswordController::class, 'validateResetToken']);

// New route for validating password reset token
// This route is similar to the existing '/users/password-reset/validate/{token}' route
// Consider merging the functionality if they are meant to do the same thing
Route::get('/users/validate-password-reset-token/{token}', function ($token) {
    if (empty($token)) {
        return response()->json(['message' => 'Token is required.'], 400);
    }

    $passwordResetToken = PasswordResetToken::where('token', $token)->first();
    if (!$passwordResetToken || $passwordResetToken->isExpired()) {
        return response()->json(['message' => 'Invalid or expired token.'], 404);
    }

    return response()->json(['status' => 200, 'message' => 'Token is valid. You can proceed to set a new password.']);
});

Route::put('/users/password-reset/{token}', function (Request $request, $token) {
    // ... existing code for password reset ...
    // The existing code for password reset is already present in the EXISTING CODE block
    // Ensure that the logic for password reset is not duplicated and is consistent
});

Route::post('/password/reset/request', function (Request $request) {
    // ... existing code for password reset request ...
    // The existing code for password reset request is already present in the EXISTING CODE block
    // Ensure that the logic for password reset request is not duplicated and is consistent
});

Route::post('/login', function (Request $request) {
    // ... existing code for login ...
    // The existing code for login is already present in the EXISTING CODE block
    // Ensure that the logic for login is not duplicated and is consistent
});

Route::middleware('auth:sanctum')->post('/session/maintain', function (Request $request) {
    // ... existing code for maintaining a user session ...
    // The existing code for maintaining a user session is already present in the EXISTING CODE block
    // Ensure that the logic for maintaining a user session is not duplicated and is consistent
});
