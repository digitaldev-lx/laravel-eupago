<?php

namespace DigitaldevLx\LaravelEupago\Events;

use DigitaldevLx\LaravelEupago\Models\GooglePayReference;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GooglePayReferencePaid
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The GooglePayReference reference object.
     *
     * @var GooglePayReference
     */
    public $reference;

    /**
     * GooglePayReferencePaid constructor.
     *
     * @param GooglePayReference $reference
     */
    public function __construct(GooglePayReference $reference)
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
