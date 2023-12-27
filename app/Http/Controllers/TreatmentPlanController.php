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

    public function declineTreatmentPlan(Request $request, $treatment_plan_id = null)
    {
        // Use DeclineTreatmentPlanRequest if it's the existing code
        if ($request instanceof DeclineTreatmentPlanRequest && $treatment_plan_id !== null) {
            // ... (existing code for declineTreatmentPlan)
        } else {
            // New code for declineTreatmentPlan without DeclineTreatmentPlanRequest
            // ... (new code for declineTreatmentPlan without DeclineTreatmentPlanRequest)
        }

        // New code for declineTreatmentPlan with route model binding and authorization
        try {
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
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Treatment plan not found.'], 404);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Internal Server Error'], 500);
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
