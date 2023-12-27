<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\TreatmentPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TreatmentPlanNotification;
use Illuminate\Support\Facades\Validator;

class ReservationController extends Controller
{
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
            'treatment_plan_id' => 'required|exists:treatment_plans,id',
            'scheduled_at' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $input = $validator->validated();

        // Check if a reservation already exists for the treatment plan
        $existingReservation = Reservation::where('treatment_plan_id', $input['treatment_plan_id'])->first();
        if ($existingReservation) {
            return response()->json(['message' => 'A reservation already exists for this treatment plan.'], 409);
        }

        // Create a new provisional reservation
        $reservation = Reservation::create([
            'treatment_plan_id' => $input['treatment_plan_id'],
            'scheduled_at' => $input['scheduled_at'],
            'status' => 'provisional',
        ]);

        // Send an email notification
        $treatmentPlan = TreatmentPlan::find($input['treatment_plan_id']);
        // Ensure that the email is sent to the Beauty Salon's email address
        // Assuming that the Beauty Salon's email address is stored in a configuration or environment variable
        $beautySalonEmail = config('mail.beauty_salon_email'); // Replace with the actual configuration key
        Mail::to($beautySalonEmail)->send(new TreatmentPlanNotification($treatmentPlan, $reservation)); // Include reservation details in the notification

        // Return the reservation details
        return response()->json([
            'id' => $reservation->id,
            'treatment_plan_id' => $reservation->treatment_plan_id,
            'status' => $reservation->status,
            'scheduled_at' => $reservation->scheduled_at,
        ], 201);
    }

    // ... (other methods in the controller)
}
