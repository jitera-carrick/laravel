<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController; // Import the ForgotPasswordController
use App\Http\Controllers\Auth\PasswordResetConfirmationController; // Import the PasswordResetConfirmationController
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// This is the existing code for the /user route within the 'auth:sanctum' middleware.
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// This is the new code for the password reset request route.
// Define a new POST route for password reset request with validation
Route::post('/users/password-reset-request', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ], [
        'email.required' => 'Email is required.',
        'email.email' => 'Invalid email format.',
        'email.exists' => 'Email not found.',
    ]);

    if ($validator->fails()) {
        $errors = $validator->errors();
        if ($errors->has('email')) {
            return response()->json([
                'status' => $errors->first('email') === 'Email not found.' ? 404 : 400,
                'message' => $errors->first('email'),
            ], $errors->first('email') === 'Email not found.' ? 404 : 400);
        }
    }

    // Assuming the ForgotPasswordController's sendResetLinkEmail method handles the response
    return app(ForgotPasswordController::class)->sendResetLinkEmail($request);
})->name('password.email');

// New route for password reset confirmation
Route::post('/users/password-reset-confirmation', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'token' => 'required',
        'password' => 'required|min:8',
    ], [
        'token.required' => 'Token is required.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 8 characters long.',
    ]);

    if ($validator->fails()) {
        $errors = $validator->errors();
        $status = 400;
        $message = 'Invalid request parameters.';

        if ($errors->has('token')) {
            $status = 404;
            $message = $errors->first('token');
        } elseif ($errors->has('password')) {
            $status = 422;
            $message = $errors->first('password');
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
        ], $status);
    }

    $status = Password::reset(
        $request->only('email', 'password', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => bcrypt($password)
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    if ($status == Password::PASSWORD_RESET) {
        return response()->json([
            'status' => 200,
            'message' => 'Password reset successfully.',
        ]);
    } else {
        return response()->json([
            'status' => 400,
            'message' => 'Invalid or expired password reset token.',
        ], 400);
    }
})->name('password.reset.confirm');
