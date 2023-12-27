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

Route::post('/users/password-reset', [ForgotPasswordController::class, 'sendPasswordResetLink']);

Route::get('/users/password-reset/validate/{token}', [ResetPasswordController::class, 'validateResetToken']);

Route::put('/users/password-reset/{token}', function (Request $request, $token) {
    // ... existing code for password reset ...
});

Route::post('/password/reset/request', function (Request $request) {
    // ... existing code for password reset request ...
});

Route::post('/login', function (Request $request) {
    // ... existing code for login ...
});

Route::middleware('auth:sanctum')->post('/session/maintain', function (Request $request) {
    // ... existing code for maintaining a user session ...
});

Route::post('/login/cancel', function () {
    return response()->json([
        'status' => 200,
        'message' => 'Login process canceled successfully.'
    ]);
});

// This route is added as per the guideline to validate the password reset token
Route::get('/api/users/password_reset/validate_token/{token}', function ($token) {
    if (empty($token)) {
        return response()->json(['message' => 'Token is required.'], 400);
    }

    $passwordResetToken = PasswordResetToken::where('token', $token)->first();
    if (!$passwordResetToken || $passwordResetToken->isExpired()) {
        return response()->json(['message' => 'Invalid or expired token.'], 404);
    }

    return response()->json(['status' => 200, 'message' => 'Token is valid. You may proceed to set a new password.']);
})->name('password.reset.validate.token');
