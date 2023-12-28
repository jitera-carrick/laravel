<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController; // Import the ForgotPasswordController
use App\Http\Controllers\Auth\PasswordResetConfirmationController; // Import the PasswordResetConfirmationController
use App\Http\Controllers\Auth\LoginController; // Import the LoginController
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

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

// New code for the user login endpoint
Route::post('/users/login', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
        'password' => 'required',
    ], [
        'email.required' => 'Email is required.',
        'email.email' => 'The email must be a valid email address.',
        'email.exists' => 'The email does not exist.',
        'password.required' => 'Password is required.',
    ]);

    if ($validator->fails()) {
        $errors = $validator->errors();
        $status = 400;
        $message = 'Invalid request parameters.';

        if ($errors->has('email')) {
            $status = $errors->first('email') === 'The email does not exist.' ? 401 : 422;
            $message = $errors->first('email');
        } elseif ($errors->has('password')) {
            $status = 422;
            $message = $errors->first('password');
        }

        return response()->json([
            'status' => $status,
            'message' => $message,
        ], $status);
    }

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json([
            'status' => 401,
            'message' => 'The password is incorrect.',
        ], 401);
    }

    $user = Auth::user();
    $token = $user->createToken('authToken')->plainTextToken;

    return response()->json([
        'status' => 200,
        'message' => 'User logged in successfully.',
        'access_token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ],
    ], 200);
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
        $request->only('email', 'password, 'token'),
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
