<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Request;
use App\Models\TreatmentPlan;
use Carbon\Carbon;

class AutoExpireHairStylistRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requests:autoexpire {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically expire hair stylist requests after a specified date and time';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $expiredRequests = [];

        try {
            $requests = Request::where('user_id', $userId)->get();

            foreach ($requests as $request) {
                $treatmentPlan = TreatmentPlan::where('user_id', $userId)
                    ->where('status', 'confirmed')
                    ->where('created_at', '<', Carbon::now())
                    ->first();

                if ($treatmentPlan) {
                    $request->status = 'expired';
                    $request->save();
                    $expiredRequests[] = [
                        'request_id' => $request->id,
                        'status' => $request->status,
                    ];
                }
            }

            foreach ($expiredRequests as $expiredRequest) {
                $this->info("Request ID {$expiredRequest['request_id']} has been set to {$expiredRequest['status']}.");
            }

            return 0; // Success
        } catch (\Exception $e) {
            $this->error("An error occurred: {$e->getMessage()}");
            return 1; // Error
        }
    }
}
