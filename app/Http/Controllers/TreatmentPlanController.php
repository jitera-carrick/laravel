<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeclineTreatmentPlanRequest;
use App\Models\TreatmentPlan;
use App\Models\Reservation;
use App\Models\Stylist;
use App\Models\User;
use App\Models\Message; // Import Message model
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

    // ... (existing methods)

    public function declineTreatmentPlan(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'treatment_plan_id' => 'required|integer|exists:treatment_plans,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $userId = $request->input('user_id');
        $treatmentPlanId = $request->input('treatment_plan_id');

        try {
            $treatmentPlan = TreatmentPlan::where('id', $treatmentPlanId)
                                          ->where('user_id', $userId)
                                          ->firstOrFail();

            if (Auth::id() !== $treatmentPlan->user_id) {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }

            DB::beginTransaction();
            $treatmentPlan->status = 'declined';
            $treatmentPlan->save();

            $reservation = Reservation::where('treatment_plan_id', $treatmentPlan->id)->first();
            if ($reservation) {
                $reservation->status = 'cancelled';
                $reservation->save();
            }

            $stylist = Stylist::find($treatmentPlan->stylist_id);
            if ($stylist && $stylist->user) {
                Mail::to($stylist->user->email)->send(new TreatmentPlanDeclinedStylist($treatmentPlan));
            }

            Log::info("Treatment plan {$treatmentPlan->id} declined at " . now());

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Treatment plan declined successfully, reservation cancelled, and stylist notified.',
                'treatment_plan' => new TreatmentPlanResource($treatmentPlan),
            ], 200);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Treatment plan not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred while declining the treatment plan.'], 500);
        }
    }

    // ... (other methods in the controller)
}
