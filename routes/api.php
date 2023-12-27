<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\HairStylistRequestController;
use App\Http\Controllers\TreatmentPlanController;
use App\Http\Controllers\ReservationController;
use App\Models\HairStylistRequest;
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

// Updated route for cancelling a hair stylist request registration
// Merged the logic from the new code into the existing route structure.
Route::middleware('auth:sanctum')->delete('/hair_stylist_requests/{id}', function (Request $request, $id) {
    if (!is_numeric($id)) {
        return response()->json([
            'status' => 422,
            'error' => 'The request body or parameters are in the wrong format.',
        ], 422);
    }

    $hairStylistRequest = HairStylistRequest::where('id', $id)->where('user_id', $request->user()->id)->first();

    if (!$hairStylistRequest) {
        return response()->json([
            'status' => 400,
            'error' => 'Request not found or you do not have permission to cancel this request.',
        ], 400);
    }

    $hairStylistRequest->delete();

    return response()->json([
        'status' => 200,
        'message' => 'Request cancelled successfully.',
    ], 200);
})->name('hair_stylist_requests.cancel');

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
