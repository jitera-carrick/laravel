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

            // The following code is for the previous functionality of the command
            // It should remain intact as per the instructions
            $cancelledPlans = [];
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
                }
            }

            return 0; // Success
        } catch (\Exception $e) {
            $this->error("An error occurred: {$e->getMessage()}");
            return 1; // Error
        }
    }
}
