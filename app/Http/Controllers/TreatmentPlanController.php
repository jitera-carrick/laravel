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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Resources\TreatmentPlanResource; // Correct namespace for TreatmentPlanResource
use Illuminate\Validation\Rule;

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
            $validator = Validator::make($request->all(), [
                'treatment_plan_id' => [
                    'required',
                    'exists:treatment_plans,id',
                    Rule::exists('treatment_plans', 'id')->where(function ($query) {
                        $query->where('status', 'waiting_for_approval');
                    }),
                ],
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $treatmentPlan = TreatmentPlan::find($request->treatment_plan_id);

            if ($treatmentPlan->status !== 'waiting_for_approval') {
                return response()->json(['error' => 'Treatment plan is not waiting for approval'], 422);
            }

            DB::transaction(function () use ($treatmentPlan) {
                $treatmentPlan->update(['status' => 'approved']);

                $reservation = $treatmentPlan->reservation;
                if ($reservation && $reservation->status === 'provisional') {
                    $reservation->update(['status' => 'confirmed']);
                }
            });

            // Send email notifications
            $customer = $treatmentPlan->user;
            $stylist = $treatmentPlan->stylist;
            $ownerEmail = config('mail.owner_email');

            Mail::to($customer->email)->send(new TreatmentPlanFixedCustomer($treatmentPlan));
            Mail::to($stylist->user->email)->send(new TreatmentPlanFixedStylist($treatmentPlan));
            Mail::to($ownerEmail)->send(new TreatmentPlanFixedOwner($treatmentPlan));

            return response()->json([
                'message' => 'Treatment plan approved successfully',
                'treatment_plan' => $treatmentPlan,
            ]);
        } else {
            // ... (existing code for approveTreatmentPlan with $id)
        }
    }

    public function declineTreatmentPlan($request, $id)
    {
        // Use DeclineTreatmentPlanRequest if it's the existing code
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

    public function autoCancelBeforeAppointment(Request $request, $id)
    {
        // ... (existing code for autoCancelBeforeAppointment)
    }

    // ... (other methods in the controller)
}
