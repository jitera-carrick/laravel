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
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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
Route::middleware('auth:sanctum')->post('/hair-stylist-requests', [HairStylistRequestController::class, 'store']);

// Updated route for cancelling a hair stylist request registration
Route::middleware('auth:sanctum')->delete('/hair_stylist_requests/{id}', [HairStylistRequestController::class, 'cancelRequest'])->name('hair_stylist_requests.cancel');

// Add new route for approving a treatment plan
Route::middleware('auth:sanctum')->put('/treatment_plans/{id}/approve', [TreatmentPlanController::class, 'approveTreatmentPlan'])->name('treatment_plans.approve');

// Update the route for declining a treatment plan with validation and business logic
// Merged the new code's validation logic into the existing route
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
Route::middleware('auth:sanctum')->post('/messages', [MessageController::class, 'sendMessageAndAdjustTreatmentPlan']);

// New route for creating a provisional reservation
Route::middleware('auth:sanctum')->post('/reservations', [ReservationController::class, 'createProvisionalReservation'])->name('reservations.createProvisional');

// New route for auto-canceling provisional reservations
Route::middleware('auth:sanctum')->put('/reservations/{id}/auto-cancel', [ReservationController::class, 'autoCancel'])->where('id', '[0-9]+');

// Add new route for auto cancelling a treatment plan
Route::put('/treatment_plans/{id}/auto_cancel', [TreatmentPlanController::class, 'autoCancel'])->withoutMiddleware('auth:sanctum')->where('id', '[0-9]+');

// New route for auto expiring hair stylist requests
Route::put('/hair-stylist-requests/auto-expire', function () {
    try {
        $expiredRequestsCount = HairStylistRequest::where('status', 'pending')
            ->where('expiration_date', '<', Carbon::now())
            ->update(['status' => 'expired']);

        return response()->json([
            'status' => 200,
            'message' => "Expired {$expiredRequestsCount} requests successfully."
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 500,
            'error' => 'An unexpected error occurred on the server.',
        ], 500);
    }
})->middleware('can:administrate')->name('hair_stylist_requests.autoExpire');

// New route for validating hair stylist request input
Route::middleware('auth:sanctum')->post('/hair-stylist-requests/validate', function (Request $request) {
    $validator = Validator::make($request->all(), [
        'area_ids' => 'required|array|min:1',
        'area_ids.*' => 'integer|exists:areas,id',
        'menu_ids' => 'required|array|min:1',
        'menu_ids.*' => 'integer|exists:menus,id',
        'hair_concerns' => 'nullable|string|max:3000',
        'images' => 'nullable|array',
        'images.*' => 'file|mimes:png,jpg,jpeg|max:5120', // 5MB
    ], [
        'area_ids.required' => 'Please select at least one area.',
        'menu_ids.required' => 'Please select at least one menu.',
        'hair_concerns.max' => 'Hair concerns text is too long.',
        'images.*.mimes' => 'Invalid image format or size.',
        'images.*.max' => 'Invalid image format or size.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 422,
            'errors' => $validator->errors(),
        ], 422);
    }

    return response()->json([
        'status' => 200,
        'message' => 'Validation successful.',
    ], 200);
});
