<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
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
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => $validator->errors()->first()], 400);
    }

    $user = User::where('email', $request->email)->first();

    if ($user) {
        $token = Str::random(60);
        DB::table('password_resets')->insert([
            'email' => $user->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        Mail::send('emails.password', ['token' => $token], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Reset Password Notification');
        });
    }

    return response()->json([
        'status' => 200,
        'message' => 'If your email address exists in our database, you will receive a password reset link.'
    ]);
})->middleware('api');

// Register and Login Routes
Route::post('/register', [LoginController::class, 'register'])->middleware('api');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
Route::post('/login/cancel', [LoginController::class, 'cancelLogin'])->middleware('api');

// Password Reset Request Route
Route::post('/password/reset/request', [ResetPasswordController::class, 'requestReset'])->middleware('api');

// Set New Password Route
Route::put('/users/password_reset/set_new_password', [ResetPasswordController::class, 'setNewPassword'])->middleware('api');
