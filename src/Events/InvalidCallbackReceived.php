<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvalidCallbackReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<string, mixed>  $callbackData
     * @param  array<string, mixed>  $errors
     */
    public function __construct(
        public readonly array $callbackData,
        public readonly array $errors,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(config('eupago.channel'));
    }
}
