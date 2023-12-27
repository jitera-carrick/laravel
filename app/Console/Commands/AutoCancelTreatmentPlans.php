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
    protected $signature = 'treatmentplans:autocancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically cancel treatment plans that have not been reconfirmed 2 hours before the scheduled time';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cancelledPlans = [];
        $now = Carbon::now();
        $twoHoursBefore = $now->copy()->subHours(2); // Corrected to subtract hours

        $treatmentPlans = TreatmentPlan::where('status', 'approved')->get();

        foreach ($treatmentPlans as $plan) {
            $reservation = Reservation::where('treatment_plan_id', $plan->id)->first();

            if ($reservation && $now->gte($reservation->scheduled_at->subHours(2)) && $now->lt($reservation->scheduled_at)) {
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
                    'cancelled_at' => $now->toDateTimeString(),
                ];
            }
        }

        return $cancelledPlans;
    }
}
