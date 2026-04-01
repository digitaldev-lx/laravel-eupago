<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Events;

use DigitaldevLx\LaravelEupago\Models\CreditCardReference;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditCardReferencePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly CreditCardReference $reference,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(config('eupago.channel'));
    }
}
