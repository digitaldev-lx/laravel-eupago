<?php

namespace DigitaldevLx\LaravelEupago\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallbackReceived
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The callback data received.
     *
     * @var array
     */
    public $callbackData;

    /**
     * The payment method type.
     *
     * @var string
     */
    public $paymentMethod;

    /**
     * CallbackReceived constructor.
     *
     * @param array $callbackData
     * @param string $paymentMethod
     */
    public function __construct(array $callbackData, string $paymentMethod)
    {
        $this->callbackData = $callbackData;
        $this->paymentMethod = $paymentMethod;
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
