<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController; // Import the ForgotPasswordController
use Illuminate\Support\Facades\Validator;

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
