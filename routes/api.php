<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RequestController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // Import the DB facade for database operations
use Illuminate\Validation\Rule; // Import the Rule facade for validation

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

// Route for getting the authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route for resetting the user's password
Route::post("/users/reset-password", [ResetPasswordController::class, "resetPassword"]);

// Route for user registration with throttle middleware
Route::post("/users/register", [RegisterController::class, "register"])->middleware("throttle:api");

// Route for validating the reset password token
Route::post('/api/auth/validate-reset-token', [ResetPasswordController::class, 'validateResetToken']);

// Route for user login
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:api');

// Send a reset link to the user's email address
Route::post('/auth/send-reset-link-email', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'email' => 'required|email|exists:users,email',
    ], [
        'email.exists' => 'Email address not found.',
        'email.email' => 'Invalid email address format.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => $validator->errors()->first() === 'Email address not found.' ? 400 : 422,
            'message' => $validator->errors()->first(),
        ], $validator->errors()->first() === 'Email address not found.' ? 400 : 422);
    }

    $response = app(ForgotPasswordController::class)->sendResetLinkEmail($request);

    return $response;
})->middleware('throttle:api');

// Route for deleting a hair stylist request image with auth and permission middleware
// This route is from the existing code and has been modified to match the new code's URI pattern and middleware
Route::middleware(['auth:sanctum', 'can:delete,request_id'])->delete('/hair-stylist-request/{request_id}/image/{image_id}', [RequestController::class, 'deleteHairStylistRequestImage'])
    ->where('request_id', '[0-9]+')
    ->where('image_id', '[0-9]+');

// New route for creating a hair stylist request
// This route is from the existing code
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/hair-stylist-request/create', [RequestController::class, 'createHairStylistRequest']);
});

// New PATCH route for updating hair stylist requests
// This route is from the new code
Route::middleware(['auth:sanctum', 'can:update,request'])->patch('/hair-stylist-request/update/{request}', function (Request $request, \App\Models\Request $requestModel) {
    $validator = Validator::make($request->all(), [
        'area' => 'sometimes|string',
        'menu' => 'sometimes|string',
        'hair_concerns' => 'sometimes|string',
        'status' => ['sometimes', Rule::in(['pending', 'confirmed', 'cancelled'])], // Assuming these are the enum values
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 422,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    $requestModel->fill($request->only(['area', 'menu', 'hair_concerns', 'status']));
    $requestModel->save();

    return response()->json([
        'status' => 200,
        'request' => $requestModel,
    ], 200);
})->where('request', '[0-9]+');

// New PATCH route for cancelling a hair stylist request
Route::middleware('auth:sanctum')->patch('/hair-stylist-request/cancel/{id}', function ($id) {
    $user = request()->user();
    $request = DB::table('requests')->where('id', $id)->first();

    if (!$request) {
        return response()->json(['message' => 'Request not found.'], 404);
    }

    if ($request->user_id !== $user->id && !$user->isAdmin()) {
        return response()->json(['message' => 'Unauthorized action.'], 401);
    }

    $updated = DB::table('requests')->where('id', $id)->update(['status' => 'cancelled', 'updated_at' => now()]);

    if (!$updated) {
        return response()->json(['message' => 'Unable to update status to cancelled.'], 500);
    }

    return response()->json([
        'status' => 200,
        'request' => [
            'id' => $id,
            'status' => 'cancelled',
            'updated_at' => now()->toIso8601String()
        ]
    ], 200);
});
