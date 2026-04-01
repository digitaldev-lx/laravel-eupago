<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Events;

use DigitaldevLx\LaravelEupago\Models\MbwayReference;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MBWayReferenceExpired
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly MbwayReference $reference,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(config('eupago.channel'));
    }
}
