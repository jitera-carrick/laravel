<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\RegisterController;
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

// Register Route
Route::post('/register', function (Request $request) {
    // Merged code for registering user account
    // Validate the input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|unique:users,email',
        'display_name' => 'required',
        'password' => [
            'required',
            'min:6',
            'different:email',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/' // Merged password validation rules
        ],
    ]);

    if ($validator->fails()) {
        return response()->json(['registration' => false, 'errors' => $validator->errors()], 400);
    }

    // Encrypt the password
    $encryptedPassword = Hash::make($request->password);

    // Create a new user entry
    $user = User::create([
        'email' => $request->email,
        'password' => $encryptedPassword,
        'display_name' => $request->display_name,
    ]);

    // Generate a unique token for password reset
    $token = Str::random(60);
    DB::table('password_resets')->insert([
        'email' => $user->email,
        'token' => $token,
        'created_at' => Carbon::now(),
    ]);

    // Send an email with the password reset link
    Mail::send('emails.register', ['token' => $token], function ($message) use ($user) {
        $message->to($user->email);
        $message->subject('Set Your Password');
    });

    // Return the user_id
    return response()->json(['user_id' => $user->id]);
})->middleware('api');

// Login Route
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');

// Cancel Login Route
Route::post('/login/cancel', [LoginController::class, 'cancelLogin'])->middleware('api');

// Password Reset Token Validation Route
Route::get('/users/password_reset/validate_token', function (Request $request) {
    $token = $request->query('token');

    // Validation
    if (empty($token)) {
        return response()->json(['message' => 'Token is required.'], 400);
    }

    $tokenRecord = DB::table('password_resets')->where('token', $token)->first();

    if (!$tokenRecord || Carbon::parse($tokenRecord->expires_at)->isPast()) {
        return response()->json(['message' => 'Invalid or expired token.'], 422);
    }

    return response()->json(['status' => 200, 'message' => 'Token is valid.']);
})->middleware('api');

// Set New Password Route
Route::put('/users/password_reset/set_new', function (Request $request) {
    // Merged code for setting a new password
    $validator = Validator::make($request->all(), [
        'token' => 'required',
        'password' => 'required|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
    ]);

    if ($validator->fails()) {
        return response()->json(['message' => $validator->errors()->first()], 400);
    }

    $tokenData = DB::table('password_resets')->where('token', $request->token)->first();

    if (!$tokenData) {
        return response()->json(['message' => 'This password reset token is invalid.'], 404);
    }

    if (Carbon::parse($tokenData->expires_at)->isPast()) {
        return response()->json(['message' => 'This password reset token has expired.'], 422);
    }

    $user = User::find($tokenData->user_id);
    if (!$user) {
        return response()->json(['message' => 'User does not exist.'], 404);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    // Delete the token
    DB::table('password_resets')->where('token', $request->token)->delete();

    return response()->json(['message' => 'Your password has been successfully reset.'], 200);
})->middleware('api');

// Add new route for password reset request
Route::post('/password/reset/request', function (Request $request) {
    // ... existing code ...
})->middleware('api');

// Add a new route to handle the PUT request for verifying email and setting the password
Route::put('/verify_email_set_password', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'token' => 'required',
        'password' => [
            'required',
            'min:6',
            'different:email',
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
        ],
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 422, 'message' => $validator->errors()->first()], 422);
    }

    $passwordReset = DB::table('password_resets')->where('token', $request->token)
                      ->where('created_at', '>', Carbon::now()->subHours(24))->first();

    if (!$passwordReset) {
        return response()->json(['status' => 400, 'message' => 'Invalid or expired token.'], 400);
    }

    $user = User::where('email', $passwordReset->email)->first();

    if (!$user) {
        return response()->json(['status' => 400, 'message' => 'User does not exist.'], 400);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    // Delete the password reset token
    DB::table('password_resets')->where('token', $request->token)->delete();

    return response()->json(['status' => 200, 'message' => 'Email verified and password set successfully.']);
})->middleware('api');
