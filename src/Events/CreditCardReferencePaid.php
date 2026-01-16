<?php

namespace DigitaldevLx\LaravelEupago\Events;

use DigitaldevLx\LaravelEupago\Models\CreditCardReference;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditCardReferencePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The CreditCardReference reference object.
     *
     * @var CreditCardReference
     */
    public $reference;

    /**
     * CreditCardReferencePaid constructor.
     *
     * @param CreditCardReference $reference
     */
    public function __construct(CreditCardReference $reference)
    {
        $this->reference = $reference;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel(config('eupago.channel'));
    }
}
