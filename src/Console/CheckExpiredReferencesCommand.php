<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Console;

use DigitaldevLx\LaravelEupago\Events\MBReferenceExpired;
use DigitaldevLx\LaravelEupago\Events\MBWayReferenceExpired;
use DigitaldevLx\LaravelEupago\Models\MbReference;
use DigitaldevLx\LaravelEupago\Models\MbwayReference;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class CheckExpiredReferencesCommand extends Command
{
    protected $signature = 'eupago:check-expired';

    protected $description = 'Check for expired payment references and dispatch events';

    public function handle(): int
    {
        $this->info('Checking for expired references...');

        $expiredMbCount = 0;
        $expiredMbwayCount = 0;

        MbReference::where('end_date', '<', now())
            ->where('state', 0)
            ->chunk(100, function ($references) use (&$expiredMbCount): void {
                foreach ($references as $reference) {
                    event(new MBReferenceExpired($reference));
                    $expiredMbCount++;
                }
            });

        if (Schema::hasColumn('mbway_references', 'end_date')) {
            MbwayReference::where('end_date', '<', now())
                ->where('state', 0)
                ->chunk(100, function ($references) use (&$expiredMbwayCount): void {
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
