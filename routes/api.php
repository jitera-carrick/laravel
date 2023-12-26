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

// New route for registering user account
Route::post('/register', function (Request $request) {
    // Validate the input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|unique:users,email',
        'display_name' => 'required',
        'password' => [
            'required',
            'min:6',
            'different:email',
            'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/' // Keep the password validation rules from the existing code
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

// Add new route for login
Route::post('/login', function (Request $request) {
    // Validate the input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json(['login' => false, 'errors' => $validator->errors()], 400);
    }

    // Query the "users" table to find a user with the matching email address
    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json(['login' => false, 'message' => 'Invalid credentials'], 401);
    }

    // Check if the "remember_token" is provided and set the session expiration accordingly
    $expiration = $request->filled('remember_token') ? Carbon::now()->addDays(90) : Carbon::now()->addDay();

    // Generate a session token
    $sessionToken = Str::random(60);

    // Update the "users" table with the new "session_token" and "session_expiration"
    $user->forceFill([
        'session_token' => $sessionToken,
        'session_expiration' => $expiration,
    ])->save();

    // Log the login attempt
    DB::table('login_attempts')->insert([
        'user_id' => $user->id,
        'attempted_at' => Carbon::now(),
        'success' => true,
    ]);

    // Return a success response with the user's session information
    return response()->json([
        'login' => true,
        'session_token' => $sessionToken,
        'session_expiration' => $expiration->toDateTimeString(),
    ]);
})->middleware('throttle:login');

// New route for canceling login
Route::post('/login/cancel', function (Request $request) {
    // Since no backend action is required, we directly return a success response.
    // Merged the response message from the new code with the status code from the existing code.
    return response()->json(['status' => 200, 'message' => 'Login canceled successfully.'], 200);
})->middleware('api');

// Add new route for password reset request
Route::post('/password/reset/request', function (Request $request) {
    // Validate the input
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => 400, 'message' => 'Invalid email format.'], 400);
    }

    // Check if the user exists
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['status' => 400, 'message' => 'Email does not exist.'], 400);
    }

    // Generate a unique token for password reset
    $token = Str::random(60);
    DB::table('password_resets')->insert([
        'email' => $user->email,
        'token' => $token,
        'created_at' => Carbon::now(),
    ]);

    // Send an email with the password reset link
    Mail::send('emails.password_reset', ['token' => $token], function ($message) use ($user) {
        $message->to($user->email);
        $message->subject('Password Reset Request');
    });

    return response()->json(['status' => 200, 'message' => 'Password reset request sent.']);
})->middleware('api');
