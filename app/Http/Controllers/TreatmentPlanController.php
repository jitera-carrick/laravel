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
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class TreatmentPlanController extends Controller
{
    // ... (other methods in the controller)

    public function approveTreatmentPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'treatment_plan_id' => 'required|exists:treatment_plans,id',
            'customer_id' => 'required|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $treatmentPlan = TreatmentPlan::where('id', $request->treatment_plan_id)
                                       ->where('user_id', $request->customer_id)
                                       ->first();

        if (!$treatmentPlan) {
            return response()->json(['error' => 'Treatment plan does not match customer ID'], 404);
        }

        $treatmentPlan->update(['status' => 'approved']);

        $reservation = Reservation::where('treatment_plan_id', $treatmentPlan->id)->first();
        if ($reservation) {
            $reservation->update(['status' => 'confirmed']);
        }

        // Send email notifications
        $customer = $treatmentPlan->user;
        $stylist = $treatmentPlan->stylist;
        $ownerEmail = config('mail.owner_email'); // Assuming the owner's email is configured in mail.php

        Mail::to($customer->email)->send(new TreatmentPlanFixedCustomer($treatmentPlan));
        Mail::to($stylist->user->email)->send(new TreatmentPlanFixedStylist($treatmentPlan));
        Mail::to($ownerEmail)->send(new TreatmentPlanFixedOwner($treatmentPlan));

        // Assuming we have a TreatmentPlanResource to format the response
        return new TreatmentPlanResource($treatmentPlan);
    }

    public function declineTreatmentPlan(DeclineTreatmentPlanRequest $request)
    {
        $treatmentPlanId = $request->input('treatment_plan_id');
        $customerId = $request->input('customer_id');

        $treatmentPlan = TreatmentPlan::find($treatmentPlanId);

        if (!$treatmentPlan) {
            return response()->json(['error' => 'Treatment plan does not exist.'], 404);
        }

        if ($treatmentPlan->user_id != $customerId) {
            return response()->json(['error' => 'Customer does not have permission to decline this treatment plan.'], 403);
        }

        $treatmentPlan->status = 'declined';
        $treatmentPlan->save();

        $reservation = Reservation::where('treatment_plan_id', $treatmentPlanId)->first();
        if ($reservation) {
            $reservation->status = 'cancelled';
            $reservation->save();
        }

        Mail::to('salon@example.com')->send(new TreatmentPlanCancelled($treatmentPlan));

        return response()->json([
            'treatment_plan_id' => $treatmentPlan->id,
            'customer_id' => $customerId,
            'status' => $treatmentPlan->status,
            'cancellation_details' => 'Treatment plan has been declined and associated reservation cancelled.'
        ]);
    }

    // ... (other methods in the controller)
}