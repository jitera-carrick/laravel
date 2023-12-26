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

// Updated route for user registration to meet the requirements
Route::post('/users/register', function (Request $request) {
    // ... existing user registration code ...
    // The new user registration logic will be merged here
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255|unique:users,email',
        'password' => 'required|string|min:8',
    ], [
        'name.required' => 'The name is required.',
        'email.required' => 'The email is required.',
        'email.email' => 'Invalid email format.',
        'email.unique' => 'Email already registered.',
        'password.required' => 'The password is required.',
        'password.min' => 'Password must be at least 8 characters long.',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    try {
        DB::beginTransaction();

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::commit();

        return response()->json([
            'status' => 201,
            'message' => 'User registered successfully.',
        ], 201);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Internal Server Error'], 500);
    }
})->middleware('throttle:6,1');

// New route for maintaining session preferences
Route::put('/maintain_session', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'session_token' => 'required|string',
        'keep_session' => 'required|boolean',
    ], [
        'session_token.required' => 'Session token is required.',
        'keep_session.required' => 'Keep session is required.',
        'keep_session.boolean' => 'Keep session must be a boolean.',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    // Assuming the SessionController has a method to handle session maintenance
    return app(SessionController::class)->updateSessionPreferences($request);
})->middleware('auth:sanctum');

// New route for updating user profile
Route::middleware('auth:sanctum')->put('/user/profile', function (Request $request) {
    // ... existing update user profile code ...
    // The new update user profile logic will be merged here
    $validator = Validator::make($request->all(), [
        'name' => 'required|string',
        'email' => 'required|email|unique:users,email,' . $request->user()->id,
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 422);
    }

    $user = $request->user();
    $user->name = $request->input('name');
    $user->email = $request->input('email');
    $user->save();

    return response()->json([
        'status' => 200,
        'message' => 'Profile updated successfully.',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'updated_at' => $user->updated_at->toDateTimeString(),
        ]
    ]);
});
