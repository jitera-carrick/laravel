<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeclineTreatmentPlanRequest;
use App\Models\TreatmentPlan;
use App\Models\Reservation;
use App\Models\Stylist;
use App\Models\User;
use App\Mail\TreatmentPlanCancelled;
use App\Mail\TreatmentPlanNotification; // New Mailable class for notifications
use App\Mail\TreatmentPlanFixedCustomer;
use App\Mail\TreatmentPlanFixedStylist;
use App\Mail\TreatmentPlanFixedOwner;
use App\Mail\TreatmentPlanDeclinedStylist; // Assuming this is the correct namespace for the new Mailable
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\TreatmentPlanResource; // Correct namespace for TreatmentPlanResource
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // Added for DB transactions
use Illuminate\Support\Facades\Log;

class TreatmentPlanController extends Controller
{
    // ... (other methods in the controller)

    public function createTreatmentPlan(Request $request)
    {
        // ... (new code for createTreatmentPlan)
    }

    public function approveTreatmentPlan(Request $request, $id = null)
    {
        // If $id is null, it means the new code is being used
        if (is_null($id)) {
            // New code for approveTreatmentPlan without $id
            // ... (new code for approveTreatmentPlan without $id)
        } else {
            // ... (existing code for approveTreatmentPlan with $id)
        }
    }

    public function declineTreatmentPlan(Request $request, $id)
    {
        if ($request instanceof DeclineTreatmentPlanRequest) {
            // ... (existing code for declineTreatmentPlan)
        } else {
            // New code for declineTreatmentPlan without DeclineTreatmentPlanRequest
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
    }

    public function cancelTreatmentPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'treatment_plan_id' => 'required|exists:treatment_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $treatmentPlan = TreatmentPlan::where('id', $request->treatment_plan_id)
                                       ->where('status', 'approved')
                                       ->first();

        if (!$treatmentPlan) {
            return response()->json(['error' => 'Treatment plan not found or not in approved status.'], 404);
        }

        $treatmentPlan->status = 'cancelled';
        $treatmentPlan->save();

        $reservation = Reservation::where('treatment_plan_id', $treatmentPlan->id)
                                  ->where('status', 'confirmed')
                                  ->first();

        if ($reservation) {
            $reservation->status = 'cancelled';
            $reservation->save();
        }

        // Assuming that the Mailable classes exist and are similar to TreatmentPlanCancelled
        Mail::to($treatmentPlan->user->email)->send(new TreatmentPlanCancelled($treatmentPlan));
        Mail::to($treatmentPlan->stylist->user->email)->send(new TreatmentPlanCancelled($treatmentPlan));
        Mail::to('salon@example.com')->send(new TreatmentPlanCancelled($treatmentPlan)); // Salon owner email

        return response()->json([
            'status' => 200,
            'message' => 'Treatment plan has been cancelled successfully.',
            'treatment_plan' => [
                'id' => $treatmentPlan->id,
                'status' => $treatmentPlan->status,
            ]
        ]);
    }

    public function autoCancelBeforeAppointment(Request $request, $id)
    {
        // ... (existing code for autoCancelBeforeAppointment)
    }

    // ... (other methods in the controller)
}
