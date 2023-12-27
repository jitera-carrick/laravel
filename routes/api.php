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

// Merged the two routes for validating the password reset token
// and kept the name for the route as per the existing code
Route::get('/users/validate-password-reset-token/{token}', function ($token) {
    if (empty($token)) {
        return response()->json(['message' => 'Token is required.'], 400);
    }

    $passwordResetToken = PasswordResetToken::where('token', $token)->first();
    if (!$passwordResetToken || $passwordResetToken->isExpired()) {
        return response()->json(['message' => 'Invalid or expired token.'], 404);
    }

    return response()->json(['status' => 200, 'message' => 'Token is valid. You can proceed to set a new password.']);
})->name('password.reset.validate.token');

// New route for setting a new password
Route::put('/users/password_reset/set_new_password', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'password' => [
            'required',
            'min:6',
            function ($attribute, $value, $fail) use ($request) {
                if ($value === $request->user()->email) {
                    $fail('Password must be different from the email address.');
                }
                if (!preg_match('/[a-zA-Z]/', $value) || !preg_match('/[0-9]/', $value)) {
                    $fail('Password must contain a mix of letters and numbers.');
                }
            },
        ],
        'token' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => $validator->errors()->first()], 422);
    }

    $token = $request->input('token');
    $passwordResetToken = PasswordResetToken::where('token', $token)->first();

    if (!$passwordResetToken || $passwordResetToken->isExpired()) {
        return response()->json(['message' => 'Invalid or expired token.'], 400);
    }

    $user = $passwordResetToken->user;
    $user->password = Hash::make($request->input('password'));
    $user->save();

    // Invalidate the token after successful password reset
    $passwordResetToken->delete();

    return response()->json(['status' => 200, 'message' => 'Your password has been successfully updated.']);
});

// New route for handling password reset errors
Route::put('/api/users/password_reset/error_handling', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'sometimes|required|email',
        'token' => 'sometimes|required|string',
        'password' => 'sometimes|required|string|min:8',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 422,
            'error' => $validator->errors(),
        ], 422);
    }

    // Assuming the business logic for handling password reset errors is implemented here
    try {
        // ... existing code for handling password reset errors ...
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => 'An unexpected error occurred on the server.',
        ], 500);
    }

    // If no errors occurred, return a successful response
    return response()->json([
        'status' => 200,
        'message' => 'Password reset error handling was successful.',
    ]);
});
