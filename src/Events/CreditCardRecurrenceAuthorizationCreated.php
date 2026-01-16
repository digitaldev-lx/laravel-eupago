<?php

namespace DigitaldevLx\LaravelEupago\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditCardRecurrenceAuthorizationCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The authorization data returned from API.
     *
     * @var array
     */
    public $authorizationData;

    /**
     * CreditCardRecurrenceAuthorizationCreated constructor.
     *
     * @param array $authorizationData
     */
    public function __construct(array $authorizationData)
    {
        $this->authorizationData = $authorizationData;
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
