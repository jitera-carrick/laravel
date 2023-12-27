<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeclineTreatmentPlanRequest;
use App\Models\TreatmentPlan;
use App\Models\Reservation;
use App\Models\Stylist;
use App\Models\User;
use App\Mail\TreatmentPlanCancelled;
use App\Mail\TreatmentPlanNotification;
use App\Mail\TreatmentPlanFixedCustomer;
use App\Mail\TreatmentPlanFixedStylist;
use App\Mail\TreatmentPlanFixedOwner;
use App\Mail\TreatmentPlanDeclinedStylist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\TreatmentPlanResource;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TreatmentPlanController extends Controller
{
    // ... (other methods in the controller)

    public function createTreatmentPlan(Request $request)
    {
        // ... (existing code for createTreatmentPlan)
        // ... (new code for createTreatmentPlan)
        // Combine the existing and new code as needed, ensuring no duplication of functionality
    }

    public function approveTreatmentPlan(Request $request, $id = null)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (is_null($id)) {
            // New code for approveTreatmentPlan without $id
            // ... (new code for approveTreatmentPlan without $id)
        } else {
            // Existing code for approveTreatmentPlan with $id
            if (!is_numeric($id)) {
                return response()->json(['error' => 'Wrong format.'], 422);
            }

            $treatmentPlan = TreatmentPlan::find($id);

            if (!$treatmentPlan) {
                return response()->json(['error' => 'Treatment plan not found.'], 404);
            }

            if ($request->user()->id !== $treatmentPlan->user_id) {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }

            try {
                DB::beginTransaction();
                $treatmentPlan->status = 'approved';
                $treatmentPlan->save();
                DB::commit();

                return response()->json([
                    'status' => 200,
                    'treatment_plan' => [
                        'id' => $treatmentPlan->id,
                        'status' => $treatmentPlan->status,
                        'updated_at' => $treatmentPlan->updated_at->toIso8601String(),
                    ]
                ], 200);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error($e->getMessage());
                return response()->json(['error' => 'Internal Server Error'], 500);
            }
        }
    }

    public function declineTreatmentPlan(Request $request, $treatment_plan_id = null)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if ($request instanceof DeclineTreatmentPlanRequest || $treatment_plan_id !== null) {
            // ... (existing code for declineTreatmentPlan)
        } else {
            // New code for declineTreatmentPlan without DeclineTreatmentPlanRequest
            // ... (new code for declineTreatmentPlan without DeclineTreatmentPlanRequest)
            // Validate the 'id' parameter
            if (!is_numeric($treatment_plan_id)) {
                return response()->json(['message' => 'Wrong format.'], 422);
            }

            $treatmentPlan = TreatmentPlan::findOrFail($treatment_plan_id);

            // Ensure the authenticated user is authorized to decline the treatment plan
            if ($treatmentPlan->user_id !== Auth::id()) {
                return response()->json(['message' => 'This action is unauthorized.'], 403);
            }

            // Update the treatment plan's status to 'declined'
            $treatmentPlan->status = 'declined';
            $treatmentPlan->save();

            // Cancel the associated reservation if it exists
            $reservation = Reservation::where('treatment_plan_id', $treatmentPlan->id)->first();
            if ($reservation) {
                $reservation->status = 'cancelled';
                $reservation->save();
            }

            // Send cancellation email to salon
            Mail::to('salon@example.com')->send(new TreatmentPlanCancelled($treatmentPlan));

            // Return the updated treatment plan data
            return response()->json([
                'status' => 200,
                'treatment_plan' => new TreatmentPlanResource($treatmentPlan)
            ], 200);
        }
    }

    public function cancelTreatmentPlan(Request $request)
    {
        // ... (existing code for cancelTreatmentPlan)
    }

    public function autoCancelBeforeAppointment(Request $request, $id)
    {
        // ... (existing code for autoCancelBeforeAppointment)
    }

    // New method for auto-canceling treatment plans
    public function autoCancelTreatmentPlan(Request $request, $id)
    {
        // Validate that $id is an integer
        if (!is_numeric($id) || intval($id) != $id) {
            return response()->json(['error' => 'Wrong format.'], 422);
        }

        try {
            $treatmentPlan = TreatmentPlan::findOrFail($id);
            $treatmentPlan->status = 'auto_cancelled';
            $treatmentPlan->save();

            return response()->json(new TreatmentPlanResource($treatmentPlan), 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Treatment plan not found.'], 400);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred while auto-canceling the treatment plan.'], 500);
        }
    }

    // ... (other methods in the controller)
}
