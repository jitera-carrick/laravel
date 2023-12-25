<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
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

// Existing routes
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// New route for password reset
Route::post('/password/email', function (Request $request) {
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
        'reset_token' => $resetToken,
        'expires_at' => $expiresAt,
    ]);
})->middleware('throttle:6,1');
