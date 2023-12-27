<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\TreatmentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TreatmentPlanNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    public function __construct()
    {
        // Ensure that the user is authenticated and authorized to create a reservation
        $this->middleware('auth');
    }

    // ... (other methods in the controller)

    /**
     * Create a provisional reservation.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function createProvisionalReservation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'treatment_plan_id' => [
                'required',
                Rule::exists('treatment_plans', 'id')->where(function ($query) {
                    $query->whereNotNull('stylist_id');
                }),
            ],
            'scheduled_at' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $input = $validator->validated();

        // Check if a reservation already exists for the treatment plan
        $existingReservation = Reservation::where('treatment_plan_id', $input['treatment_plan_id'])
                                          ->where('status', 'provisional')
                                          ->first();
        if ($existingReservation) {
            return response()->json(['message' => 'A provisional reservation already exists for this treatment plan.'], 409);
        }

        try {
            // Create a new provisional reservation
            $reservation = Reservation::create([
                'treatment_plan_id' => $input['treatment_plan_id'],
                'status' => 'provisional', // Set status to provisional
                'scheduled_at' => $input['scheduled_at'],
            ]);

            // Send an email notification
            $treatmentPlan = TreatmentPlan::find($input['treatment_plan_id']);
            $beautySalonEmail = config('mail.beauty_salon_email');
            Mail::to($beautySalonEmail)->send(new TreatmentPlanNotification($treatmentPlan, $reservation));

            // Return the reservation details
            return response()->json([
                'status' => 200,
                'reservation' => [
                    'id' => $reservation->id,
                    'treatment_plan_id' => $reservation->treatment_plan_id,
                    'status' => $reservation->status,
                    'scheduled_at' => $reservation->scheduled_at,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to create provisional reservation: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create provisional reservation.'], 500);
        }
    }

    // ... (other methods in the controller)
}
