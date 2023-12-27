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
            $validator = Validator::make($request->all(), [
                'treatment_plan_id' => [
                    'required',
                    'exists:treatment_plans,id',
                    Rule::exists('treatment_plans', 'id')->where(function ($query) {
                        $query->where('status', 'awaiting_approval');
                    }),
                ],
                'user_id' => 'required|exists:users,id' // Changed from 'customer_id' to 'user_id' to match new code
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            try {
                $treatmentPlan = TreatmentPlan::where('id', $request->treatment_plan_id)
                    ->where('status', 'awaiting_approval')
                    ->firstOrFail();

                if (Auth::id() !== $request->user_id) {
                    return response()->json(['error' => 'You do not have permission to approve this treatment plan.'], 403);
                }

                DB::transaction(function () use ($treatmentPlan) {
                    $treatmentPlan->update([
                        'status' => 'approved',
                        'updated_at' => now()
                    ]);

                    $reservation = Reservation::where('treatment_plan_id', $treatmentPlan->id)
                        ->where('status', 'provisional')
                        ->firstOrFail();

                    $reservation->update([
                        'status' => 'confirmed',
                        'updated_at' => now()
                    ]);
                });

                // Send email notifications
                $customer = $treatmentPlan->user;
                $stylist = $treatmentPlan->stylist;
                $ownerEmail = config('mail.owner_email'); // Changed to use config instead of hardcoded email

                Mail::to($customer->email)->send(new TreatmentPlanFixedCustomer($treatmentPlan));
                Mail::to($stylist->user->email)->send(new TreatmentPlanFixedStylist($treatmentPlan));
                Mail::to($ownerEmail)->send(new TreatmentPlanFixedOwner($treatmentPlan));

                return new TreatmentPlanResource($treatmentPlan);
            } catch (ModelNotFoundException $e) {
                Log::error('Treatment plan approval failed: ' . $e->getMessage());
                return response()->json(['error' => 'Treatment plan not found or already approved.'], 404);
            } catch (\Exception $e) {
                Log::error('Unexpected error during treatment plan approval: ' . $e->getMessage());
                return response()->json(['error' => 'An unexpected error occurred.'], 500);
            }
        } else {
            // ... (existing code for approveTreatmentPlan with $id)
        }
    }

    public function declineTreatmentPlan(Request $request, $id = null)
    {
        // Use DeclineTreatmentPlanRequest if it's the existing code
        if ($request instanceof DeclineTreatmentPlanRequest || $id !== null) {
            // ... (existing code for declineTreatmentPlan)
        } else {
            // New code for declineTreatmentPlan without DeclineTreatmentPlanRequest
            $treatmentPlanId = $request->input('treatment_plan_id');

            // Validate that the treatment plan exists and is waiting for approval
            $treatmentPlan = TreatmentPlan::where('id', $treatmentPlanId)
                                          ->where('status', 'waiting_for_approval')
                                          ->first();

            if (!$treatmentPlan) {
                return response()->json(['error' => 'Treatment plan does not exist or is not waiting for approval.'], 404);
            }

            // Update the status of the treatment plan to 'declined'
            $treatmentPlan->update(['status' => 'declined']);

            // Find the linked provisional reservation and update its status to 'cancelled'
            $reservation = Reservation::where('treatment_plan_id', $treatmentPlanId)->first();
            if ($reservation) {
                $reservation->update(['status' => 'cancelled']);
            }

            // Send an email notification to the Hair Stylist associated with the declined treatment plan
            $stylist = Stylist::where('id', $treatmentPlan->stylist_id)->first();
            if ($stylist && $stylist->user) {
                Mail::to($stylist->user->email)->send(new TreatmentPlanCancelled($treatmentPlan));
            }

            // Return a confirmation response with the updated status
            return response()->json([
                'treatment_plan_id' => $treatmentPlan->id,
                'status' => $treatmentPlan->status,
                'cancellation_details' => 'Treatment plan has been declined and associated reservation cancelled.'
            ]);
        }
    }

    public function autoCancelBeforeAppointment(Request $request, $id)
    {
        // ... (existing code for autoCancelBeforeAppointment)
    }

    // ... (other methods in the controller)
}
