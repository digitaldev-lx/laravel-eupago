<?php

namespace DigitaldevLx\LaravelEupago\Events;

use DigitaldevLx\LaravelEupago\Models\ApplePayReference;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApplePayReferencePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The ApplePayReference reference object.
     *
     * @var ApplePayReference
     */
    public $reference;

    /**
     * ApplePayReferencePaid constructor.
     *
     * @param ApplePayReference $reference
     */
    public function __construct(ApplePayReference $reference)
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
