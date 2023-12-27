<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeclineTreatmentPlanRequest;
use App\Models\TreatmentPlan;
use App\Models\Reservation;
use App\Mail\TreatmentPlanCancelled;
use App\Mail\TreatmentPlanFixedCustomer;
use App\Mail\TreatmentPlanFixedStylist;
use App\Mail\TreatmentPlanFixedOwner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\TreatmentPlanResource; // Assuming this is the correct namespace for TreatmentPlanResource

class TreatmentPlanController extends Controller
{
    // ... (other methods in the controller)

    public function approveTreatmentPlan(Request $request, $id = null)
    {
        if ($id === null) {
            // ... (existing code for approveTreatmentPlan without $id)
            // This part is from the existing code where $id is not used.
        } else {
            // New code for approveTreatmentPlan with $id
            if (!is_numeric($id)) {
                return response()->json(['error' => 'Wrong format.'], 400);
            }

            try {
                $treatmentPlan = TreatmentPlan::findOrFail($id);
            } catch (ModelNotFoundException $e) {
                return response()->json(['error' => 'Treatment plan not found.'], 404);
            }

            if (Auth::id() !== $treatmentPlan->user_id) {
                return response()->json(['error' => 'You do not have permission to approve this treatment plan.'], 403);
            }

            $treatmentPlan->update(['status' => 'approved']);

            // Assuming we have a TreatmentPlanResource to format the response
            return new TreatmentPlanResource($treatmentPlan);
        }
    }

    public function declineTreatmentPlan(DeclineTreatmentPlanRequest $request, $id)
    {
        // ... (existing code for declineTreatmentPlan)
    }

    public function autoCancelBeforeAppointment(Request $request, $id)
    {
        $validator = Validator::make($request->all() + ['id' => $id], [
            'id' => 'required|integer|exists:treatment_plans,id',
            'current_time' => 'required|date_format:Y-m-d\TH:i:s\Z'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $treatmentPlan = TreatmentPlan::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Treatment plan not found.'], 404);
        }

        $currentTime = new \DateTime($request->current_time);
        $appointmentTime = new \DateTime($treatmentPlan->reservations->first()->scheduled_at);

        if ($currentTime >= $appointmentTime->modify('-2 hours') && $treatmentPlan->status !== 'approved') {
            $treatmentPlan->update(['status' => 'canceled']);

            $reservation = Reservation::where('treatment_plan_id', $treatmentPlan->id)->first();
            if ($reservation) {
                $reservation->update(['status' => 'cancelled']);
            }

            Mail::to('salon@example.com')->send(new TreatmentPlanCancelled($treatmentPlan));

            // Assuming we have a TreatmentPlanResource to format the response
            return new TreatmentPlanResource($treatmentPlan);
        }

        return response()->json(['message' => 'No action required.'], 200);
    }

    // ... (other methods in the controller)
}
