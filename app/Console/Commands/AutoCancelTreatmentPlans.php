<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TreatmentPlan;
use App\Models\Reservation;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

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
            $canceled_plans = TreatmentPlan::where('status', '!=', 'approved')
                ->where('created_at', '<', $cutoff_time)
                ->update(['status' => 'canceled']);

            $this->info("Successfully canceled {$canceled_plans} unapproved treatment plans.");

            // New functionality to cancel provisional reservations
            $cancelledPlans = [];
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

                $cancelledPlans[] = [
                    'treatment_plan_id' => $treatmentPlan->id,
                    'reservation_id' => $reservation->id,
                    'cancelled_at' => $current_time->toDateTimeString(),
                ];
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
                    $cancelledPlans[] = [
                        'treatment_plan_id' => $plan->id,
                        'reservation_id' => $reservation->id,
                        'cancelled_at' => $current_time->toDateTimeString(),
                    ];
                    $this->info("Treatment Plan ID {$plan->id} with Reservation ID {$reservation->id} was cancelled at {$current_time->toDateTimeString()}.");
                }
            }

            // The following code is for the previous functionality of the command
            // It should remain intact as per the instructions
            $twoHoursBefore = $current_time->copy()->subHours(2);

            $treatmentPlans = TreatmentPlan::where('status', 'approved')->get();

            foreach ($treatmentPlans as $plan) {
                $reservation = Reservation::where('treatment_plan_id', $plan->id)->first();

                if ($reservation && $current_time->gte($reservation->scheduled_at->subHours(2)) && $current_time->lt($reservation->scheduled_at)) {
                    $plan->status = 'cancelled';
                    $plan->save();

                    $reservation->status = 'cancelled';
                    $reservation->save();

                    // Send email notifications to the Customer, Hair Stylist, and Beauty Salon
                    // Assuming that the email templates and user retrieval logic are already defined
                    $customer = $plan->user;
                    $stylist = $plan->stylist;
                    $salon = 'salon@example.com'; // Placeholder for salon email

                    Mail::to($customer->email)->send(new \App\Mail\TreatmentPlanCancelledCustomer($plan, $reservation));
                    Mail::to($stylist->email)->send(new \App\Mail\TreatmentPlanCancelledStylist($plan, $reservation));
                    Mail::to($salon)->send(new \App\Mail\TreatmentPlanCancelledOwner($plan, $reservation));

                    $cancelledPlans[] = [
                        'treatment_plan_id' => $plan->id,
                        'reservation_id' => $reservation->id,
                        'cancelled_at' => $current_time->toDateTimeString(),
                    ];
                    $this->info("Treatment Plan ID {$plan->id} with Reservation ID {$reservation->id} was cancelled at {$current_time->toDateTimeString()}.");
                }
            }

            return 0; // Success
        } catch (\Exception $e) {
            $this->error("An error occurred: {$e->getMessage()}");
            return 1; // Error
        }
    }
}
