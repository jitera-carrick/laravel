<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TreatmentPlanController;
use App\Http\Controllers\HairStylistRequestController;

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

// New route for creating a hair stylist request
Route::middleware('auth:sanctum')->post('/hair_stylist_requests', [HairStylistRequestController::class, 'createHairStylistRequest']);

// Add new route for cancelling a hair stylist request
Route::middleware('auth:sanctum')->delete('/hair_stylist_requests/{id}', [HairStylistRequestController::class, 'cancelRequest']);

// Added new route for declining a treatment plan
Route::middleware('auth:sanctum')->put('/treatment_plans/{id}/decline', [TreatmentPlanController::class, 'declineTreatmentPlan']);
