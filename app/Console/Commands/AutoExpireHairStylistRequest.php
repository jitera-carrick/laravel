<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Request;
use Illuminate\Support\Facades\Log;

class AutoExpireHairStylistRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requests:autoexpire';

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
        try {
            $requestsToExpire = Request::shouldExpire()->get();

            foreach ($requestsToExpire as $request) {
                $request->status = 'expired';
                $request->save();
                $this->info("Request ID {$request->id} has been set to expired.");
            }

            $this->info("All applicable requests have been successfully expired.");

            return 0; // Success
        } catch (\Exception $e) {
            Log::error("An error occurred while expiring requests: {$e->getMessage()}");
            $this->error("An error occurred: {$e->getMessage()}");
            return 1; // Error
        }
    }
}
