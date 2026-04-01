<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplePayReferenceCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $referenceData
     */
    public function __construct(
        public readonly array $referenceData,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(config('eupago.channel'));
    }
}
