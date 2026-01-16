<?php

namespace DigitaldevLx\LaravelEupago\Events;

use DigitaldevLx\LaravelEupago\Models\MbReference;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MBReferenceExpired
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The expired MbReference reference object.
     *
     * @var MbReference
     */
    public $reference;

    /**
     * MBReferenceExpired constructor.
     *
     * @param MbReference $reference
     */
    public function __construct(MbReference $reference)
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
