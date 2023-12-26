<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController; // Import the LoginController
use App\Http\Controllers\SessionController; // Import SessionController
use App\Http\Controllers\VerificationController; // Add this line to use VerificationController
use App\Http\Controllers\Auth\ForgotPasswordController;

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

// Existing routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// New route for password reset request
Route::post('/password/email', function (Request $request) {
    // ... existing password reset code ...
    // The new password reset logic will be merged here
    // ... existing code ...
    // Start of new password reset request logic
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ], [
        'email.required' => 'Email is required.',
        'email.email' => 'Invalid email format.',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $user = User::where('email', $request->input('email'))->first();

    if (!$user) {
        return response()->json(['message' => 'Email not found.'], 404);
    }

    $resetToken = Str::random(60);
    $expiresAt = Carbon::now()->addHours(24);

    DB::table('password_reset_requests')->insert([
        'user_id' => $user->id,
        'reset_token' => $resetToken,
        'created_at' => Carbon::now(),
        'expires_at' => $expiresAt,
    ]);

    // Send email logic (pseudo-code)
    Mail::send('emails.password_reset', ['token' => $resetToken], function ($message) use ($user) {
        $message->to($user->email);
        $message->subject('Password Reset Request');
    });

    return response()->json([
        'status' => 200,
        'message' => 'Password reset request sent successfully.',
        'token' => $resetToken,
    ]);
    // End of new password reset request logic
})->middleware('throttle:6,1');

// New route for email verification
Route::post('/email/verify', function (Request $request) {
    // ... existing email verification code ...
    // The new email verification logic will be merged here
    // ... existing code ...
})->middleware('throttle:6,1');

// Updated route for user registration to meet the requirements
Route::post('/users/register', function (Request $request) {
    // ... existing user registration code ...
    // The new user registration logic will be merged here
    // ... existing code ...
})->middleware('throttle:6,1');

// New route for user login
Route::post('/users/login', function (Request $request) {
    // ... existing user login code ...
    // The new user login logic will be merged here
    // ... existing code ...
});

// New route for maintaining session preferences
Route::put('/maintain_session', function (Request $request) {
    // ... existing code for maintaining session preferences ...
    // The new maintain session preferences logic will be merged here
    // ... existing code ...
});

// New route for updating user profile
Route::middleware('auth:sanctum')->put('/user/profile', function (Request $request) {
    // ... existing update user profile code ...
    // The new update user profile logic will be merged here
    // ... existing code ...
});

// New route for user logout
Route::delete('/users/logout', function (Request $request) {
    // ... existing user logout code ...
    // The new user logout logic will be merged here
    // ... existing code ...
})->middleware('auth:sanctum');

// New route for handling login failure
Route::post('/login_failure', function (Request $request) {
    // ... existing login failure handling code ...
    // The new login failure handling logic will be merged here
    // ... existing login failure handling code ...
});

// Remove the old registration route as it is now redundant
// Route::post('/user/register', function (Request $request) {
//     ...
// });
