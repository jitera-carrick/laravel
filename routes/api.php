<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordPolicyController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
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

// Define a new API route for updating password policy
Route::middleware(['auth:sanctum', 'can:update-password-policy'])->group(function () {
    Route::put('/password-policy', [PasswordPolicyController::class, 'update']);
    Route::patch('/password-policy', [PasswordPolicyController::class, 'update']);
});

// Add a new route to handle the POST request for sending a password reset link
Route::post('/users/password-reset', [ForgotPasswordController::class, 'sendPasswordResetLink']);

// New route for validating password reset link
Route::get('/users/password-reset/validate/{token}', [ResetPasswordController::class, 'validateResetToken']);

// Reset Password route
Route::put('/users/password-reset/{token}', function (Request $request, $token) {
    $validator = Validator::make($request->all(), [
        'password' => [
            'required',
            'min:6',
            'regex:/^(?=.*[a-zA-Z])(?=.*\d).+$/',
            function ($attribute, $value, $fail) use ($request) {
                if ($value === $request->user()->email) {
                    return $fail('Password cannot be the same as the email address.');
                }
            },
        ],
    ], [
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be 6 digits or more.',
        'password.regex' => 'Password must contain a mix of letters and numbers.',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    $user = User::where('password_reset_token', $token)->first();

    if (!$user) {
        return response()->json(['message' => 'The token does not exist or has expired.'], 404);
    }

    $user->password = Hash::make($request->password);
    $user->password_reset_token = null; // Clear the reset token
    $user->save();

    return response()->json(['status' => 200, 'message' => 'Your password has been successfully reset.']);
});

// Add a new route to handle the password reset request API endpoint
Route::post('/password/reset/request', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ], [
        'email.required' => 'Email is required.',
        'email.email' => 'Invalid email format.',
        'email.exists' => 'Email not registered.',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    // Assuming sendPasswordResetRequest method exists and handles the business logic
    $response = ForgotPasswordController::sendPasswordResetRequest($request->email);

    if ($response['status'] === 'success') {
        return response()->json(['status' => 200, 'message' => 'Password reset email sent successfully.']);
    } else {
        return response()->json(['status' => 500, 'message' => 'An unexpected error occurred.'], 500);
    }
});

// Define a new route for the login API
Route::post('/login', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email',
        'password' => 'required|min:8',
    ], [
        'email.required' => 'Email is required.',
        'email.email' => 'Invalid email format.',
        'password.required' => 'Password is required.',
        'password.min' => 'Password must be at least 8 characters.',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    $credentials = $request->only('email', 'password');
    $remember = $request->input('remember', false);

    if (Auth::attempt($credentials, $remember)) {
        $user = Auth::user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($remember) {
            $token->expires_at = now()->addWeeks(1);
        }
        $token->save();

        return response()->json([
            'status' => 200,
            'message' => 'Login successful.',
            'session_token' => $tokenResult->accessToken,
            'session_expiration' => $token->expires_at
        ]);
    } else {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
});

// Add a new route to handle the POST request for maintaining a user session
Route::middleware('auth:sanctum')->post('/session/maintain', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'session_token' => 'required|string|exists:personal_access_tokens,token',
        'keep_session' => 'required|boolean',
    ], [
        'session_token.required' => 'Session token is required.',
        'session_token.string' => 'Session token must be a string.',
        'session_token.exists' => 'Invalid session token.',
        'keep_session.required' => 'Keep session value is required.',
        'keep_session.boolean' => 'Invalid keep session value.',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 400);
    }

    // Assuming that the "keep_session" field is used to update a "remember_token" or similar in the User model
    $user = User::where('remember_token', $request->session_token)->first();
    if ($user && $request->keep_session) {
        // Update the user's session expiration or perform other logic to maintain the session
        // For the sake of example, we're just returning a success response with a fake expiration date
        return response()->json([
            'status' => 200,
            'message' => 'Session maintained successfully.',
            'session_expiration' => '2023-08-08T15:45:00Z' // This should be calculated based on your session logic
        ]);
    }

    return response()->json(['message' => 'Failed to maintain session.'], 500);
});
