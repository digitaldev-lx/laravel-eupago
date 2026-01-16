<?php

namespace DigitaldevLx\LaravelEupago\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvalidCallbackReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The callback data received.
     *
     * @var array
     */
    public $callbackData;

    /**
     * The validation errors.
     *
     * @var array
     */
    public $errors;

    /**
     * InvalidCallbackReceived constructor.
     *
     * @param array $callbackData
     * @param array $errors
     */
    public function __construct(array $callbackData, array $errors)
    {
        $this->callbackData = $callbackData;
        $this->errors = $errors;
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
