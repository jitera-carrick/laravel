<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TreatmentPlan;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;

class AutoCancelUnapprovedTreatmentPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'treatmentplans:autocancelunapproved';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancel unapproved treatment plans after 48 hours';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cancelledPlans = [];
        $now = Carbon::now();
        $cutoffTime = $now->copy()->subHours(48);

        try {
            $treatmentPlans = TreatmentPlan::where('status', '<>', 'approved')
                                            ->where('created_at', '<', $cutoffTime)
                                            ->get();

            foreach ($treatmentPlans as $plan) {
                $plan->status = 'canceled';
                $plan->save();

                // Optionally, send an email notification to the customer and stylist
                // Assuming that the email templates and user retrieval logic are already defined
                $customer = $plan->user;
                $stylist = $plan->stylist;

                // Check if Mailable classes exist before attempting to send emails
                if (class_exists(\App\Mail\TreatmentPlanCancelledCustomer::class) && $customer) {
                    Mail::to($customer->email)->send(new \App\Mail\TreatmentPlanCancelledCustomer($plan));
                }
                if (class_exists(\App\Mail\TreatmentPlanCancelledStylist::class) && $stylist) {
                    Mail::to($stylist->email)->send(new \App\Mail\TreatmentPlanCancelledStylist($plan));
                }

                $cancelledPlans[] = [
                    'id' => $plan->id,
                    'stylist_id' => $plan->stylist_id,
                    'user_id' => $plan->user_id,
                    'status' => $plan->status,
                    'details' => $plan->details,
                    'created_at' => $plan->created_at->toDateTimeString(),
                ];
            }

            return response()->json([
                'status' => 'success',
                'cancelled_treatment_plans' => $cancelledPlans
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred while cancelling treatment plans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
