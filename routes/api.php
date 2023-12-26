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
})->middleware('throttle:6,1');

// New route for email verification
Route::post('/email/verify', function (Request $request) {
    // ... existing email verification code ...
})->middleware('throttle:6,1');

// Updated route for user registration to meet the requirements
Route::post('/users/register', function (Request $request) {
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

// Remove the old registration route as it is now redundant
// Route::post('/user/register', function (Request $request) {
//     ...
// });
