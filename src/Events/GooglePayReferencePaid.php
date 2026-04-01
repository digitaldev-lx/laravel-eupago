<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Events;

use DigitaldevLx\LaravelEupago\Models\GooglePayReference;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GooglePayReferencePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly GooglePayReference $reference,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(config('eupago.channel'));
    }
}
