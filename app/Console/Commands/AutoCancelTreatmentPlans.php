<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TreatmentPlan;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class AutoCancelTreatmentPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'treatmentplans:autocancel {current_time?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancels unapproved treatment plans after 48 hours';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Retrieve the 'current_time' argument if provided, otherwise use the current system time
        $current_time = $this->argument('current_time') ? new Carbon($this->argument('current_time')) : Carbon::now();
        $cutoff_time = $current_time->copy()->subHours(48);

        try {
            // Cancel unapproved treatment plans that are older than 48 hours
            $canceled_plans = TreatmentPlan::where('status', 'awaiting_approval')
                ->whereHas('reservations', function ($query) use ($current_time) {
                    $query->where('scheduled_at', '<=', $current_time->copy()->subHours(2));
                })
                ->orWhere(function ($query) use ($cutoff_time) {
                    $query->where('status', 'awaiting_approval')
                          ->where('created_at', '<', $cutoff_time);
                })
                ->get();

            foreach ($canceled_plans as $plan) {
                $plan->status = 'auto_cancelled';
                $plan->save();

                $reservation = $plan->reservation; // Assuming reservation relationship is correctly set up in TreatmentPlan model
                if ($reservation) {
                    $reservation->status = 'cancelled';
                    $reservation->save();

                    // Assuming Mailable classes exist as per the guideline
                    $customer = $plan->user;
                    $stylist = $plan->stylist;
                    Mail::to($customer->email)->send(new \App\Mail\TreatmentPlanAutoCancelledCustomer($plan));
                    Mail::to($stylist->user->email)->send(new \App\Mail\TreatmentPlanAutoCancelledStylist($plan));
                    $this->info("Treatment Plan ID {$plan->id} with Reservation ID {$reservation->id} was auto-cancelled at {$current_time->toDateTimeString()}.");
                }
            }

            // New functionality to cancel provisional reservations
            $reservations = Reservation::whereHas('treatmentPlan', function ($query) use ($cutoff_time) {
                $query->where('status', 'waiting_for_approval')
                      ->where('created_at', '<', $cutoff_time);
            })->where('status', 'provisional')->get();

            foreach ($reservations as $reservation) {
                $treatmentPlan = $reservation->treatmentPlan;
                $treatmentPlan->update(['status' => 'cancelled']);
                $reservation->update(['status' => 'cancelled']);

                // Send email notifications
                $customer = $treatmentPlan->user;
                $stylist = $treatmentPlan->stylist;
                Mail::to($customer->email)->send(new \App\Mail\TreatmentPlanCancelledCustomer($treatmentPlan, $reservation));
                Mail::to($stylist->user->email)->send(new \App\Mail\TreatmentPlanCancelledStylist($treatmentPlan, $reservation));

                $this->info("Treatment Plan ID {$treatmentPlan->id} with Reservation ID {$reservation->id} was cancelled at {$current_time->toDateTimeString()}.");
            }

            // New code for cancelling treatment plans waiting for approval within 2 hours of the appointment
            $twoHoursBefore = $current_time->copy()->subHours(2);
            $waitingForApprovalPlans = TreatmentPlan::where('status', 'waiting_for_approval')
                ->whereHas('reservations', function ($query) use ($twoHoursBefore) {
                    $query->where('scheduled_at', '<=', $twoHoursBefore);
                })->get();

            foreach ($waitingForApprovalPlans as $plan) {
                $plan->update(['status' => 'cancelled']);
                $reservation = Reservation::where('treatment_plan_id', $plan->id)->first();
                if ($reservation) {
                    $reservation->update(['status' => 'cancelled']);
                    // Assuming Mailable classes exist as per the guideline
                    $customer = $plan->user;
                    $stylist = $plan->stylist;
                    Mail::to($customer->email)->send(new \App\Mail\TreatmentPlanCancelledCustomer($plan, $reservation));
                    Mail::to($stylist->user->email)->send(new \App\Mail\TreatmentPlanCancelledStylist($plan, $reservation));
                    $this->info("Treatment Plan ID {$plan->id} with Reservation ID {$reservation->id} was cancelled at {$current_time->toDateTimeString()}.");
                }
            }

            $this->info("Successfully auto-cancelled treatment plans that were awaiting approval.");

            return 0; // Success
        } catch (Exception $e) {
            Log::error("An error occurred while auto-cancelling treatment plans: {$e->getMessage()}");
            $this->error("An error occurred: {$e->getMessage()}");
            return 1; // Error
        }
    }
}
