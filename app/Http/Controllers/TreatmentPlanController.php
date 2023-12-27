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

    public function declineTreatmentPlan(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!is_numeric($id)) {
            return response()->json(['error' => 'Wrong format.'], 422);
        }

        $treatmentPlan = TreatmentPlan::find($id);

        if (!$treatmentPlan) {
            return response()->json(['error' => 'Treatment plan not found.'], 404);
        }

        $userId = Auth::id();

        if ($treatmentPlan->user_id != $userId) {
            return response()->json(['error' => 'User does not have permission to decline this treatment plan.'], 403);
        }

        $treatmentPlan->status = 'declined';
        $treatmentPlan->save();

        $reservation = Reservation::where('treatment_plan_id', $treatmentPlan->id)->first();
        if ($reservation) {
            $reservation->status = 'cancelled';
            $reservation->save();
        }

        Mail::to('salon@example.com')->send(new TreatmentPlanCancelled($treatmentPlan));

        return response()->json([
            'status' => 200,
            'treatment_plan' => [
                'id' => $treatmentPlan->id,
                'stylist_id' => $treatmentPlan->stylist_id,
                'customer_id' => $treatmentPlan->user_id,
                'status' => $treatmentPlan->status,
                'details' => $treatmentPlan->details,
                'created_at' => $treatmentPlan->created_at->toIso8601String(),
            ]
        ]);
    }

    // ... (other methods in the controller)
}
