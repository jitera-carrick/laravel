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
Route::middleware('auth:sanctum')->post('/hair_stylist_requests', [HairStylistRequestController::class, 'createHairStylistRequest']);

// Add new route for cancelling a hair stylist request
Route::middleware('auth:sanctum')->delete('/hair_stylist_requests/{id}', [HairStylistRequestController::class, 'cancelRequest']);

// Add new route for approving a treatment plan
Route::middleware('auth:sanctum')->put('/treatment_plans/{id}/approve', [TreatmentPlanController::class, 'approveTreatmentPlan'])->name('treatment_plans.approve');

// Update the route for declining a treatment plan with validation and business logic
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
    Route::post('/messages', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'sender_id' => 'required|exists:users,id',
            'receiver_id' => 'required|exists:users,id',
            'content' => 'required|max:500',
            'sent_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Assuming MessageController exists and has the sendMessageAndAdjustTreatmentPlan method
            $messageController = new MessageController();
            return $messageController->sendMessageAndAdjustTreatmentPlan($request);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 400,
                'error' => 'User not found.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'An unexpected error occurred on the server.',
            ], 500);
        }
    });

    // New route for creating a provisional reservation
    Route::post('/reservations', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'scheduled_at' => 'required|date|after:now',
            'treatment_plan_id' => 'required|exists:treatment_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Assuming ReservationController exists and has the createProvisionalReservation method
            $reservationController = new ReservationController();
            return $reservationController->createProvisionalReservation($request);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 400,
                'error' => 'Treatment plan not found.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'An unexpected error occurred on the server.',
            ], 500);
        }
    })->name('reservations.createProvisional');
});
