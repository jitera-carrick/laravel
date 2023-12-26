<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\SessionController; // Import SessionController
use App\Http\Controllers\VerificationController; // Add this line to use VerificationController

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

// New route for password reset
Route::post('/password/email', function (Request $request) {
    // ... existing password reset code ...
    // The new password reset logic will be merged here
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $user = User::where('email', $request->input('email'))->first();

    if (!$user) {
        return response()->json(['message' => 'User does not exist.'], 404);
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
        'message' => 'Password reset email has been sent.',
        'reset_token' => $resetToken,
        'expires_at' => $expiresAt->toDateTimeString(),
    ]);
})->middleware('throttle:6,1');

// New route for email verification
Route::post('/email/verify', function (Request $request) {
    // ... existing email verification code ...
    // The new email verification logic will be merged here
    $validator = Validator::make($request->all(), [
        'id' => 'required|integer',
        'verification_token' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $user = User::find($request->input('id'));

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    $verificationToken = $request->input('verification_token');
    if ($user->verification_token === $verificationToken) {
        $user->email_verified_at = Carbon::now();
        $user->save();

        return response()->json(['message' => 'Email verified successfully.']);
    } else {
        return response()->json(['message' => 'Email verification failed.'], 400);
    }
})->middleware('throttle:6,1');

// New route for user email verification using VerificationController
// This route is redundant with the '/email/verify' route and should be removed or refactored.
// For now, we will comment it out to avoid conflicts.
/*
Route::post('/user/verify-email', function (Request $request) {
    // ... existing user email verification code ...
    // This code is redundant and has been commented out.
});
*/

// Updated route for user registration to meet the requirements
Route::post('/users/register', function (Request $request) {
    // ... existing user registration code ...
    // The new user registration logic will be merged here
    // ... existing user registration code ...
});

// New route for maintaining session preferences
Route::put('/maintain_session', function (Request $request) {
    // ... existing maintain session preferences code ...
    // The new maintain session preferences logic will be merged here
    // ... existing maintain session preferences code ...
});

// New route for updating user profile
Route::middleware('auth:sanctum')->put('/user/profile', function (Request $request) {
    // ... existing update user profile code ...
    // The new update user profile logic will be merged here
    // ... existing update user profile code ...
});

// New route for handling login failure
Route::post('/login_failure', function (Request $request) {
    // ... existing login failure handling code ...
    // The new login failure handling logic will be merged here
    // ... existing login failure handling code ...
});
