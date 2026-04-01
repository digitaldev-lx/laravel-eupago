<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplePayReferenceCreationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string|int, string>  $errors
     * @param  array<string, mixed>  $parameters
     */
    public function __construct(
        public readonly array $errors,
        public readonly array $parameters = [],
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(config('eupago.channel'));
    }
}
