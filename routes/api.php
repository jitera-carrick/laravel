<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

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

// Existing route for getting user details
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// New route for updating shop details
Route::middleware('auth:sanctum')->match(['put', 'patch'], '/shop/update', 'ShopController@updateShop');

// New route for user registration
Route::post('/users/register', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8',
    ], [
        'name.required' => 'The name is required.',
        'email.required' => 'The email is required.',
        'email.email' => 'Invalid email format.',
        'email.unique' => 'Email already registered.',
        'password.required' => 'The password is required.',
        'password.min' => 'Password must be at least 8 characters.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 400,
            'message' => 'Bad Request',
            'errors' => $validator->errors(),
        ], 400);
    }

    // Assuming User model exists and has a create method to handle user creation.
    $user = \App\Models\User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password), // Password should be hashed
    ]);

    return response()->json([
        'status' => 201,
        'message' => 'User registered successfully.',
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->toIso8601String(),
        ],
    ], 201);
});
