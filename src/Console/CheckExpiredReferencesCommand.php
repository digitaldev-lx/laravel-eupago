<?php

namespace DigitaldevLx\LaravelEupago\Console;

use DigitaldevLx\LaravelEupago\Events\MBReferenceExpired;
use DigitaldevLx\LaravelEupago\Events\MBWayReferenceExpired;
use DigitaldevLx\LaravelEupago\Models\MbReference;
use DigitaldevLx\LaravelEupago\Models\MbwayReference;
use Illuminate\Console\Command;

class CheckExpiredReferencesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'eupago:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired payment references and dispatch events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired references...');

        $expiredMbCount = 0;
        $expiredMbwayCount = 0;

        // Check expired MB references
        MbReference::where('end_date', '<', now())
            ->where('state', 0)
            ->chunk(100, function ($references) use (&$expiredMbCount) {
                foreach ($references as $reference) {
                    event(new MBReferenceExpired($reference));
                    $expiredMbCount++;
                }
            });

        // Check expired MBWay references (if they have an end_date field)
        // Note: Current migration doesn't include end_date for MBWay,
        // but we'll keep this for future compatibility
        if (\Schema::hasColumn('mbway_references', 'end_date')) {
            MbwayReference::where('end_date', '<', now())
                ->where('state', 0)
                ->chunk(100, function ($references) use (&$expiredMbwayCount) {
                    foreach ($references as $reference) {
                        event(new MBWayReferenceExpired($reference));
                        $expiredMbwayCount++;
                    }
                });
        }

        $this->info("Found {$expiredMbCount} expired MB references.");
        $this->info("Found {$expiredMbwayCount} expired MBWay references.");
        $this->info('Expired reference events dispatched successfully.');

        return 0;
    }
}
