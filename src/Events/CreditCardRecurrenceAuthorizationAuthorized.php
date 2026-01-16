<?php

namespace DigitaldevLx\LaravelEupago\Events;

use DigitaldevLx\LaravelEupago\Models\CreditCardRecurrenceAuthorization;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditCardRecurrenceAuthorizationAuthorized
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The CreditCardRecurrenceAuthorization object.
     *
     * @var CreditCardRecurrenceAuthorization
     */
    public $authorization;

    /**
     * CreditCardRecurrenceAuthorizationAuthorized constructor.
     *
     * @param CreditCardRecurrenceAuthorization $authorization
     */
    public function __construct(CreditCardRecurrenceAuthorization $authorization)
    {
        $this->authorization = $authorization;
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
