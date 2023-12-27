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

        // Validate the ID if it's provided
        if ($id !== null) {
            $validator = Validator::make(['id' => $id], [
                'id' => 'required|integer|exists:treatment_plans,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 422,
                    'errors' => $validator->errors(),
                ], 422);
            }
        }

        try {
            if ($id !== null) {
                $treatmentPlan = TreatmentPlan::findOrFail($id);
            } else {
                // New code for approveTreatmentPlan without $id
                // ... (new code for approveTreatmentPlan without $id)
            }

            if (Auth::id() !== $treatmentPlan->user_id) {
                return response()->json(['error' => 'Unauthorized.'], 401);
            }

            DB::beginTransaction();
            $treatmentPlan->status = 'approved';
            $treatmentPlan->save();
            DB::commit();

            return response()->json([
                'status' => 200,
                'treatment_plan' => new TreatmentPlanResource($treatmentPlan),
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Treatment plan not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
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
    public function autoCancelUnapprovedTreatmentPlan(Request $request, $id)
    {
        // Validate that $id is an integer
        if (!is_numeric($id) || intval($id) != $id) {
            return response()->json(['error' => 'Wrong format.'], 422);
        }

        try {
            $treatmentPlan = TreatmentPlan::findOrFail($id);
            if ($treatmentPlan->status !== 'approved') {
                DB::beginTransaction();
                $treatmentPlan->status = 'auto_cancelled';
                $treatmentPlan->save();
                DB::commit();

                // Cancel the associated reservation if it exists
                $reservation = Reservation::where('treatment_plan_id', $treatmentPlan->id)->first();
                if ($reservation) {
                    $reservation->status = 'cancelled';
                    $reservation->save();
                }

                return response()->json(new TreatmentPlanResource($treatmentPlan), 200);
            } else {
                return response()->json(['message' => 'No action taken. Treatment plan is already approved.'], 200);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Treatment plan not found.'], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json(['error' => 'An error occurred while auto-canceling the treatment plan.'], 500);
        }
    }

    // New method sendMessageAndAdjustTreatmentPlan
    public function sendMessageAndAdjustTreatmentPlan(Request $request)
    {
        // Ensure user is authenticated and authorized
        $this->middleware('auth');

        // Validate the request parameters
        $validatedData = $request->validate([
            'content' => 'required|string|max:500',
            'user_id' => 'required|integer|exists:users,id',
            'stylist_id' => 'required|integer|exists:stylists,id',
        ], [
            'content.required' => 'The content of the message is required.',
            'content.max' => 'You cannot input more than 500 characters.',
            'user_id.required' => 'The user ID is required.',
            'user_id.exists' => 'User not found.',
            'stylist_id.required' => 'The stylist ID is required.',
            'stylist_id.exists' => 'Stylist not found.',
        ]);

        // Check if the authenticated user is the same as the user_id provided
        if (Auth::id() != $validatedData['user_id']) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Start transaction
            DB::beginTransaction();

            // Create a new message
            $message = new Message();
            $message->content = $validatedData['content'];
            $message->user_id = $validatedData['user_id'];
            $message->sent_at = now();
            $message->read = false;
            $message->save();

            // Find the relevant treatment plan and adjust it
            $treatmentPlan = TreatmentPlan::where('user_id', $validatedData['user_id'])
                                ->where('stylist_id', $validatedData['stylist_id'])
                                ->firstOrFail();
            // Update the treatment plan as necessary
            // Assuming we are just updating the status for the example
            $treatmentPlan->status = 'adjusted';
            $treatmentPlan->save();

            // Commit transaction
            DB::commit();

            // Return the newly created message details
            return response()->json([
                'status' => 200,
                'message' => [
                    'id' => $message->id,
                    'content' => $message->content,
                    'sent_at' => $message->sent_at->toIso8601String(),
                    'user_id' => $message->user_id,
                    'stylist_id' => $validatedData['stylist_id'],
                ]
            ], 200);
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ... (other methods in the controller)
}
