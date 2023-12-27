<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordPolicyController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\PasswordResetToken;
use App\Models\User;
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

Route::get('/users/password-reset/validate/{token}', [ResetPasswordController::class, 'validateResetToken'])->name('password.reset.validate.token');

Route::put('/users/password-reset/{token}', function (Request $request, $token) {
    // ... existing code for password reset ...
});

Route::post('/password/reset/request', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => $validator->errors()->first()], 400);
    }

    $user = User::where('email', $request->input('email'))->first();

    if (!$user) {
        return response()->json(['message' => 'Account not found.'], 404);
    }

    $user->sendPasswordResetNotification();

    return response()->json(['status' => 200, 'message' => 'Password reset request sent successfully.']);
});

Route::post('/login', [LoginController::class, 'login']);

Route::middleware('auth:sanctum')->post('/session/maintain', function (Request $request) {
    // ... existing code for maintaining a user session ...
});

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

    $passwordResetToken->delete();

    return response()->json(['status' => 200, 'message' => 'Your password has been successfully updated.']);
});

Route::post('/login/cancel', function () {
    return response()->json([
        'status' => 200,
        'message' => 'Login process canceled successfully.'
    ]);
});

// Updated the initiate password reset route to include proper validation and response as per the requirement
Route::post('/users/password_reset/initiate', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        $errorMessages = $validator->errors()->getMessages();
        $responseMessage = 'Invalid request parameters.';
        if (isset($errorMessages['email'])) {
            $responseMessage = $errorMessages['email'][0] === 'The email field is required.' ? 'Email address is required.' : 'Invalid email address format.';
        }
        return response()->json(['message' => $responseMessage], 422);
    }

    // Assuming ForgotPasswordController has a method initiatePasswordReset that handles the business logic
    return app(ForgotPasswordController::class)->initiatePasswordReset($request);
})->name('password.reset.initiate');
