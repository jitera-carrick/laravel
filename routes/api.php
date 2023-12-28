<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController; // Import the ForgotPasswordController
use App\Http\Controllers\RegisterController; // Import the RegisterController
use Illuminate\Support\Facades\Validator;

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

// New code for user registration route with validation
Route::post('/users/register', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
    ], [
        'name.required' => 'The name is required.',
        'email.required' => 'Email is required.',
        'email.email' => 'Invalid email format.',
        'email.unique' => 'Email already registered.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 8 characters long.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => $validator->errors()->first(),
        ], 400);
    }

    // Assuming the RegisterController's register method handles the user registration and response
    return app(RegisterController::class)->register($request);
})->name('register');
