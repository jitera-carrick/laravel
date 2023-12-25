<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Add new route for sending reset password link
Route::post('/password/email', function (Request $request) {
    // Validate the input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        return response()->json(['reset_requested' => false, 'errors' => $validator->errors()], 400);
    }

    // Query the "users" table to find a user with the matching email address
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['reset_requested' => false, 'message' => 'User not found'], 404);
    }

    // Generate a password reset token and store it
    $token = Str::random(60);
    DB::table('password_resets')->insert([
        'email' => $user->email,
        'token' => $token,
        'created_at' => Carbon::now(),
    ]);

    // Send a password reset email to the user
    Mail::send('emails.password_reset', ['token' => $token], function ($message) use ($user) {
        $message->to($user->email);
        $message->subject('Password Reset Request');
    });

    // Return a success response
    return response()->json(['reset_requested' => true]);
})->middleware('api');

// Updated route for sending reset password link to use ForgotPasswordController
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('api');

// New route for registering user account
Route::post('/register', function (Request $request) {
    // Validate the input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|unique:users,email',
        'display_name' => 'required',
        'password' => 'required',
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
    DB::table('password_reset_requests')->insert([
        'user_id' => $user->id,
        'token' => $token,
        'expires_at' => Carbon::now()->addHours(24),
    ]);

    // Send an email with the password reset link
    Mail::send('emails.register', ['token' => $token], function ($message) use ($user) {
        $message->to($user->email);
        $message->subject('Set Your Password');
    });

    // Return the user_id
    return response()->json(['user_id' => $user->id]);
})->middleware('api');
