<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\HairStylistRequestController;
use App\Http\Controllers\TreatmentPlanController;
use App\Http\Controllers\ReservationController;
use App\Models\TreatmentPlan;
use App\Models\Reservation;

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

// New route for creating a hair stylist request
// The new code has a conflicting route with a different URI and controller method.
// We will keep the original route and add the new one as an alternative.
Route::middleware('auth:sanctum')->post('/hair_stylist_requests', [HairStylistRequestController::class, 'createHairStylistRequest']);
Route::middleware('auth:sanctum')->post('/hair-stylist-requests', [HairStylistRequestController::class, 'store']);

// Add new route for cancelling a hair stylist request
Route::middleware('auth:sanctum')->delete('/hair_stylist_requests/{id}', [HairStylistRequestController::class, 'cancelRequest']);

// Add new route for approving a treatment plan
Route::middleware('auth:sanctum')->put('/treatment_plans/{id}/approve', [TreatmentPlanController::class, 'approveTreatmentPlan'])->name('treatment_plans.approve');

// Update the route for declining a treatment plan with validation and business logic
// This route is updated to include the logic from the new code while maintaining the existing route's structure.
Route::middleware('auth:sanctum')->put('/treatment_plans/{id}/decline', function (Request $request, $id) {
    if (!is_numeric($id)) {
        return response()->json([
            'status' => 422,
            'error' => 'Wrong format.',
        ], 422);
    }

    $treatmentPlan = TreatmentPlan::find($id);

    if (!$treatmentPlan) {
        return response()->json([
            'status' => 400,
            'error' => 'Treatment plan not found.',
        ], 400);
    }

    if ($request->user()->id !== $treatmentPlan->user_id) {
        return response()->json([
            'status' => 403,
            'error' => 'User does not have permission to decline the treatment plan.',
        ], 403);
    }

    $treatmentPlan->status = 'declined';
    $treatmentPlan->save();

    return response()->json([
        'status' => 200,
        'treatment_plan' => $treatmentPlan,
    ], 200);
})->name('treatment_plans.decline');

// Add new route for auto cancelling treatment plans before appointment
Route::middleware('auth:sanctum')->put('/treatment_plans/{id}/auto_cancel_before_appointment', [TreatmentPlanController::class, 'autoCancelBeforeAppointment']);

// Adding new route for POST request to `/api/messages`
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/messages', [MessageController::class, 'sendMessageAndAdjustTreatmentPlan']);

    // New route for creating a provisional reservation
    Route::post('/reservations', [ReservationController::class, 'createProvisionalReservation'])->name('reservations.createProvisional');
});

// New route for auto-canceling provisional reservations
Route::middleware('auth:sanctum')->put('/reservations/{id}/auto-cancel', function (Request $request, $id) {
    if (!is_numeric($id)) {
        return response()->json([
            'status' => 422,
            'error' => 'Wrong format.',
        ], 422);
    }

    $reservation = Reservation::find($id);

    if (!$reservation) {
        return response()->json([
            'status' => 400,
            'error' => 'Reservation not found.',
        ], 400);
    }

    $reservation->status = 'auto-canceled';
    $reservation->save();

    return response()->json([
        'status' => 200,
        'reservation' => $reservation,
    ], 200);
})->where('id', '[0-9]+');
