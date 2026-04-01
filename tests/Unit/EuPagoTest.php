<?php

declare(strict_types=1);

use DigitaldevLx\LaravelEupago\EuPago;

it('returns test endpoint when environment is test', function () {
    config(['eupago.env' => 'test']);

    $eupago = new EuPago;

    expect($eupago->getBaseUri())->toBe('https://sandbox.eupago.pt');
});

it('returns production endpoint when environment is prod', function () {
    config(['eupago.env' => 'prod']);

    $eupago = new EuPago;

    expect($eupago->getBaseUri())->toBe('https://clientes.eupago.pt');
});

it('defaults to test endpoint when environment is not set', function () {
    config(['eupago.env' => null]);

    $eupago = new EuPago;

    expect($eupago->getBaseUri())->toBe('https://sandbox.eupago.pt');
});
