<?php

namespace DigitaldevLx\LaravelEupago\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditCardReferenceCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The reference data returned from API.
     *
     * @var array
     */
    public $referenceData;

    /**
     * CreditCardReferenceCreated constructor.
     *
     * @param array $referenceData
     */
    public function __construct(array $referenceData)
    {
        $this->referenceData = $referenceData;
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
