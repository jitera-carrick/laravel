<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\LoginController; // Use LoginController
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
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

// Password Reset Routes
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/password/reset', [ResetPasswordController::class, 'reset']);

// New route for password reset initiation
Route::post('/users/password_reset/initiate', function (Request $request) {
    // ... existing code from the new code ...
})->middleware('api');

// Register and Login Routes
// Use the existing '/register' route from the old code, but update the closure to handle the new code's logic
Route::post('/register', function (Request $request) {
    // ... new code for registering user account ...
})->middleware('api');

// Use the existing '/login' route from the old code, but update the closure to handle the new code's logic
Route::post('/login', function (Request $request) {
    // ... new code for login ...
})->middleware('throttle:login');

// Use the existing '/login/cancel' route from the old code
Route::post('/login/cancel', [LoginController::class, 'cancelLogin'])->middleware('api');

// Password Reset Request Route
// Use the existing '/password/reset/request' route from the old code, but update the closure to handle the new code's logic
Route::post('/password/reset/request', function (Request $request) {
    // ... new code for password reset request ...
})->middleware('api');

// Set New Password Route
// Use the existing '/users/password_reset/set_new_password' route from the old code
Route::put('/users/password_reset/set_new_password', [ResetPasswordController::class, 'setNewPassword'])->middleware('api');

// New route for authenticating user login
Route::post('/api/authenticate_user_login', function (Request $request) {
    // ... new code for authenticating user login ...
})->middleware('api');

// Define a new route that handles the POST request to '/api/users' and points to the `register` method in the RegisterController.
// This route is conflicting with the '/register' route. We need to decide which one to keep or merge the logic.
// For this example, I'm assuming we keep the '/register' route and remove this one.
// If you need to keep this route, you should merge the logic with the '/register' route and handle it accordingly.
// Route::post('/api/users', [RegisterController::class, 'register'])->middleware('api'); // This line is commented out to avoid conflict with the '/register' route.
