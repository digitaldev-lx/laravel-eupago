<?php

namespace DigitaldevLx\LaravelEupago\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreditCardRecurrenceAuthorizationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The errors returned from API.
     *
     * @var array
     */
    public $errors;

    /**
     * The request parameters.
     *
     * @var array
     */
    public $parameters;

    /**
     * CreditCardRecurrenceAuthorizationFailed constructor.
     *
     * @param array $errors
     * @param array $parameters
     */
    public function __construct(array $errors, array $parameters = [])
    {
        $this->errors = $errors;
        $this->parameters = $parameters;
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
