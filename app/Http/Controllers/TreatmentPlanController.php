<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeclineTreatmentPlanRequest;
use App\Models\TreatmentPlan;
use App\Models\Reservation;
use App\Models\Stylist;
use App\Models\User;
use App\Models\Message;
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
    }

    public function approveTreatmentPlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'treatment_plan_id' => 'required|integer|exists:treatment_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user_id = $request->input('user_id');
        $treatment_plan_id = $request->input('treatment_plan_id');

        if (!Auth::check() || Auth::id() != $user_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $treatmentPlan = TreatmentPlan::with('reservation')->findOrFail($treatment_plan_id);

        if ($user_id !== $treatmentPlan->user_id) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::beginTransaction();
        try {
            $treatmentPlan->status = 'approved';
            $treatmentPlan->save();

            $reservation = $treatmentPlan->reservation;
            if ($reservation && $reservation->status === 'provisional') {
                $reservation->status = 'confirmed';
                $reservation->save();
            }

            Mail::to(Auth::user()->email)->send(new TreatmentPlanFixedCustomer($treatmentPlan));
            if ($treatmentPlan->stylist && $treatmentPlan->stylist->user) {
                Mail::to($treatmentPlan->stylist->user->email)->send(new TreatmentPlanFixedStylist($treatmentPlan));
            }
            Mail::to(config('mail.owner_email'))->send(new TreatmentPlanFixedOwner($treatmentPlan));

            Log::info("Treatment plan {$treatmentPlan->id} approved by user {$user_id} at " . now()->toDateTimeString());

            DB::commit();

            return response()->json([
                'message' => 'Treatment plan approved successfully.',
                'treatment_plan' => new TreatmentPlanResource($treatmentPlan),
                'reservation_status' => $reservation->status ?? 'not found',
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred while approving the treatment plan.'], 500);
        }
    }

    // ... (other methods in the controller)

    // ... (rest of the existing code)
}
