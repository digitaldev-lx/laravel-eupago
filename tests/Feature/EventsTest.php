<?php

declare(strict_types=1);

use DigitaldevLx\LaravelEupago\Events\CallbackReceived;
use DigitaldevLx\LaravelEupago\Events\InvalidCallbackReceived;
use DigitaldevLx\LaravelEupago\Events\MBReferenceCreated;
use DigitaldevLx\LaravelEupago\Events\MBReferenceCreationFailed;
use DigitaldevLx\LaravelEupago\Events\MBReferenceExpired;
use DigitaldevLx\LaravelEupago\Events\MBReferencePaid;
use DigitaldevLx\LaravelEupago\Events\MBWayReferenceCreated;
use DigitaldevLx\LaravelEupago\Events\MBWayReferenceCreationFailed;
use DigitaldevLx\LaravelEupago\Events\MBWayReferenceExpired;
use DigitaldevLx\LaravelEupago\Events\MBWayReferencePaid;
use DigitaldevLx\LaravelEupago\Models\MbReference;
use DigitaldevLx\LaravelEupago\Models\MbwayReference;

it('can instantiate MBReferenceCreated event', function () {
    $data = ['reference' => '123', 'value' => 100];
    $event = new MBReferenceCreated($data);

    expect($event->referenceData)->toBe($data);
});

it('can instantiate MBWayReferenceCreated event', function () {
    $data = ['reference' => '456', 'value' => 50];
    $event = new MBWayReferenceCreated($data);

    expect($event->referenceData)->toBe($data);
});

it('can instantiate MBReferenceCreationFailed event', function () {
    $errors = ['error' => 'Invalid data'];
    $params = ['value' => 100];
    $event = new MBReferenceCreationFailed($errors, $params);

    expect($event->errors)->toBe($errors)
        ->and($event->parameters)->toBe($params);
});

it('can instantiate MBWayReferenceCreationFailed event', function () {
    $errors = ['error' => 'Invalid phone'];
    $params = ['alias' => '123'];
    $event = new MBWayReferenceCreationFailed($errors, $params);

    expect($event->errors)->toBe($errors)
        ->and($event->parameters)->toBe($params);
});

it('can instantiate MBReferencePaid event', function () {
    $reference = MbReference::factory()->make();
    $event = new MBReferencePaid($reference);

    expect($event->reference)->toBe($reference);
});

it('can instantiate MBWayReferencePaid event', function () {
    $reference = MbwayReference::factory()->make();
    $event = new MBWayReferencePaid($reference);

    expect($event->reference)->toBe($reference);
});

it('can instantiate MBReferenceExpired event', function () {
    $reference = MbReference::factory()->make();
    $event = new MBReferenceExpired($reference);

    expect($event->reference)->toBe($reference);
});

it('can instantiate MBWayReferenceExpired event', function () {
    $reference = MbwayReference::factory()->make();
    $event = new MBWayReferenceExpired($reference);

    expect($event->reference)->toBe($reference);
});

it('can instantiate CallbackReceived event', function () {
    $data = ['mp' => 'PC:PT', 'valor' => 100];
    $method = 'PC:PT';
    $event = new CallbackReceived($data, $method);

    expect($event->callbackData)->toBe($data)
        ->and($event->paymentMethod)->toBe($method);
});

it('can instantiate InvalidCallbackReceived event', function () {
    $data = ['mp' => 'INVALID'];
    $errors = ['mp' => 'Invalid payment method'];
    $event = new InvalidCallbackReceived($data, $errors);

    expect($event->callbackData)->toBe($data)
        ->and($event->errors)->toBe($errors);
});
