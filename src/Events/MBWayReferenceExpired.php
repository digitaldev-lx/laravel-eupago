<?php

namespace DigitaldevLx\LaravelEupago\Events;

use DigitaldevLx\LaravelEupago\Models\MbwayReference;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MBWayReferenceExpired
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The expired MbwayReference reference object.
     *
     * @var MbwayReference
     */
    public $reference;

    /**
     * MBWayReferenceExpired constructor.
     *
     * @param MbwayReference $reference
     */
    public function __construct(MbwayReference $reference)
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
