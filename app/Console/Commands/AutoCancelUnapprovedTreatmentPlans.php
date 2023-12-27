<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TreatmentPlan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
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
        $now = Carbon::now();
        $cutoffTime = $now->copy()->subHours(48);
        $autoCancelledCount = 0;

        try {
            $treatmentPlans = TreatmentPlan::where('status', '<>', 'approved')
                                            ->whereHas('reservations', function ($query) use ($cutoffTime) {
                                                $query->where('scheduled_at', '<', $cutoffTime)
                                                      ->where('status', '=', 'provisional');
                                            })
                                            ->with('reservations')
                                            ->get();

            foreach ($treatmentPlans as $plan) {
                $reservation = $plan->reservations->first();
                if ($reservation) {
                    $reservation->status = 'cancelled';
                    $reservation->save();
                }

                $plan->status = 'cancelled';
                $plan->save();

                // Send a notification email to the customer and hair stylist
                $customer = $plan->user;
                $stylist = $plan->stylist;
                if ($customer && $customer->email) {
                    Mail::to($customer->email)->send(new \App\Mail\TreatmentPlanCancelledCustomer($plan));
                }
                if ($stylist && $stylist->email) {
                    Mail::to($stylist->email)->send(new \App\Mail\TreatmentPlanCancelledStylist($plan));
                }

                Log::info("Treatment plan ID {$plan->id} auto-cancelled at {$now}");

                $autoCancelledCount++;
            }

            $this->info("Successfully auto-cancelled {$autoCancelledCount} treatment plans.");

            return 0; // Success
        } catch (Exception $e) {
            Log::error("Error auto-cancelling treatment plans: {$e->getMessage()}");
            $this->error('An unexpected error occurred while cancelling treatment plans.');

            return 1; // Error
        }
    }
}
