<?php

use DigitaldevLx\LaravelEupago\Events\ApplePayReferencePaid;
use DigitaldevLx\LaravelEupago\Events\CallbackReceived;
use DigitaldevLx\LaravelEupago\Events\CreditCardReferencePaid;
use DigitaldevLx\LaravelEupago\Events\GooglePayReferencePaid;
use DigitaldevLx\LaravelEupago\Events\MBReferencePaid;
use DigitaldevLx\LaravelEupago\Events\MBWayReferencePaid;
use DigitaldevLx\LaravelEupago\Models\ApplePayReference;
use DigitaldevLx\LaravelEupago\Models\CreditCardReference;
use DigitaldevLx\LaravelEupago\Models\GooglePayReference;
use DigitaldevLx\LaravelEupago\Models\MbReference;
use DigitaldevLx\LaravelEupago\Models\MbwayReference;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
    config(['eupago.api_key' => 'test-api-key', 'eupago.channel' => 'demo']);
});

it('processes MB reference payment callback successfully', function () {
    $reference = MbReference::factory()->create([
        'reference' => '123456789',
        'value' => 100.00,
        'state' => 0,
    ]);

    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 100.00,
        'canal' => 'demo',
        'referencia' => '123456789',
        'transacao' => 'TRX123456',
        'identificador' => 1,
        'mp' => 'PC:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:10:00:00',
        'entidade' => '11249',
        'comissao' => 0.50,
        'local' => 'ATM',
    ]));

    $response->assertStatus(200)
        ->assertJson(['response' => 'Success']);

    $reference->refresh();
    expect($reference->state)->toBe(1)
        ->and($reference->transaction_id)->toBe('TRX123456');

    Event::assertDispatched(CallbackReceived::class);
    Event::assertDispatched(MBReferencePaid::class);
});

it('processes MBWay reference payment callback successfully', function () {
    $reference = MbwayReference::factory()->create([
        'reference' => 987654321,
        'value' => 50.00,
        'state' => 0,
    ]);

    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 50.00,
        'canal' => 'demo',
        'referencia' => '987654321',
        'transacao' => 'TRX999888',
        'identificador' => 2,
        'mp' => 'MW:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:11:00:00',
        'entidade' => '11249',
        'comissao' => 0.25,
        'local' => 'MOBILE',
    ]));

    $response->assertStatus(200)
        ->assertJson(['response' => 'Success']);

    $reference->refresh();
    expect($reference->state)->toBe(1);

    Event::assertDispatched(CallbackReceived::class);
    Event::assertDispatched(MBWayReferencePaid::class);
});

it('returns 404 when MB reference not found', function () {
    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 100.00,
        'canal' => 'demo',
        'referencia' => '999999999',
        'transacao' => 'TRX123456',
        'identificador' => 1,
        'mp' => 'PC:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:10:00:00',
        'entidade' => '11249',
        'comissao' => 0.50,
        'local' => 'ATM',
    ]));

    $response->assertStatus(404)
        ->assertJson(['response' => 'No pending reference found']);

    Event::assertDispatched(CallbackReceived::class);
});

it('returns 404 when MBWay reference not found', function () {
    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 50.00,
        'canal' => 'demo',
        'referencia' => '111111111',
        'transacao' => 'TRX999888',
        'identificador' => 2,
        'mp' => 'MW:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:11:00:00',
        'entidade' => '11249',
        'comissao' => 0.25,
        'local' => 'MOBILE',
    ]));

    $response->assertStatus(404);
});

it('does not update already paid reference', function () {
    $reference = MbReference::factory()->paid()->create([
        'reference' => '123456789',
        'value' => 100.00,
        'transaction_id' => 'ORIGINAL_TRX',
    ]);

    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 100.00,
        'canal' => 'demo',
        'referencia' => '123456789',
        'transacao' => 'NEW_TRX',
        'identificador' => 1,
        'mp' => 'PC:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:10:00:00',
        'entidade' => '11249',
        'comissao' => 0.50,
        'local' => 'ATM',
    ]));

    $response->assertStatus(404);

    $reference->refresh();
    expect($reference->transaction_id)->toBe('ORIGINAL_TRX');
});

it('validates callback with wrong API key', function () {
    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 100.00,
        'canal' => 'demo',
        'referencia' => '123456789',
        'transacao' => 'TRX123456',
        'identificador' => 1,
        'mp' => 'PC:PT',
        'chave_api' => 'wrong-key',
        'data' => '2024-01-01:10:00:00',
        'entidade' => '11249',
        'comissao' => 0.50,
        'local' => 'ATM',
    ]));

    $response->assertStatus(302);
});

it('validates callback with wrong channel', function () {
    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 100.00,
        'canal' => 'wrong-channel',
        'referencia' => '123456789',
        'transacao' => 'TRX123456',
        'identificador' => 1,
        'mp' => 'PC:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:10:00:00',
        'entidade' => '11249',
        'comissao' => 0.50,
        'local' => 'ATM',
    ]));

    $response->assertStatus(302);
});

it('processes Credit Card payment callback successfully', function () {
    $reference = CreditCardReference::factory()->create([
        'reference' => 'CC-123456789',
        'value' => 250.00,
        'state' => 0,
    ]);

    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 250.00,
        'canal' => 'demo',
        'referencia' => 'CC-123456789',
        'transacao' => 'TRX-CC-789456',
        'identificador' => 3,
        'mp' => 'CC:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:12:00:00',
        'entidade' => '11249',
        'comissao' => 1.50,
        'local' => 'ONLINE',
    ]));

    $response->assertStatus(200)
        ->assertJson(['response' => 'Success']);

    $reference->refresh();
    expect($reference->state)->toBe(1)
        ->and($reference->callback_transaction_id)->toBe('TRX-CC-789456');

    Event::assertDispatched(CallbackReceived::class);
    Event::assertDispatched(CreditCardReferencePaid::class);
});

it('returns 404 when Credit Card reference not found', function () {
    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 250.00,
        'canal' => 'demo',
        'referencia' => 'CC-999999999',
        'transacao' => 'TRX-CC-123',
        'identificador' => 3,
        'mp' => 'CC:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:12:00:00',
        'entidade' => '11249',
        'comissao' => 1.50,
        'local' => 'ONLINE',
    ]));

    $response->assertStatus(404)
        ->assertJson(['response' => 'No pending reference found']);

    Event::assertDispatched(CallbackReceived::class);
});

it('does not update already paid Credit Card reference', function () {
    $reference = CreditCardReference::factory()->paid()->create([
        'reference' => 'CC-123456789',
        'value' => 250.00,
        'callback_transaction_id' => 'ORIGINAL_CC_TRX',
    ]);

    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 250.00,
        'canal' => 'demo',
        'referencia' => 'CC-123456789',
        'transacao' => 'NEW_CC_TRX',
        'identificador' => 3,
        'mp' => 'CC:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:12:00:00',
        'entidade' => '11249',
        'comissao' => 1.50,
        'local' => 'ONLINE',
    ]));

    $response->assertStatus(404);

    $reference->refresh();
    expect($reference->callback_transaction_id)->toBe('ORIGINAL_CC_TRX');
});

it('processes Google Pay payment callback successfully', function () {
    $reference = GooglePayReference::factory()->create([
        'reference' => 'GP-123456789',
        'value' => 350.00,
        'state' => 0,
    ]);

    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 350.00,
        'canal' => 'demo',
        'referencia' => 'GP-123456789',
        'transacao' => 'TRX-GP-789456',
        'identificador' => 4,
        'mp' => 'GP:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:13:00:00',
        'entidade' => '11249',
        'comissao' => 2.00,
        'local' => 'ONLINE',
    ]));

    $response->assertStatus(200)
        ->assertJson(['response' => 'Success']);

    $reference->refresh();
    expect($reference->state)->toBe(1)
        ->and($reference->callback_transaction_id)->toBe('TRX-GP-789456');

    Event::assertDispatched(CallbackReceived::class);
    Event::assertDispatched(GooglePayReferencePaid::class);
});

it('returns 404 when Google Pay reference not found', function () {
    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 350.00,
        'canal' => 'demo',
        'referencia' => 'GP-999999999',
        'transacao' => 'TRX-GP-123',
        'identificador' => 4,
        'mp' => 'GP:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:13:00:00',
        'entidade' => '11249',
        'comissao' => 2.00,
        'local' => 'ONLINE',
    ]));

    $response->assertStatus(404)
        ->assertJson(['response' => 'No pending reference found']);

    Event::assertDispatched(CallbackReceived::class);
});

it('does not update already paid Google Pay reference', function () {
    $reference = GooglePayReference::factory()->paid()->create([
        'reference' => 'GP-123456789',
        'value' => 350.00,
        'callback_transaction_id' => 'ORIGINAL_GP_TRX',
    ]);

    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 350.00,
        'canal' => 'demo',
        'referencia' => 'GP-123456789',
        'transacao' => 'NEW_GP_TRX',
        'identificador' => 4,
        'mp' => 'GP:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:13:00:00',
        'entidade' => '11249',
        'comissao' => 2.00,
        'local' => 'ONLINE',
    ]));

    $response->assertStatus(404);

    $reference->refresh();
    expect($reference->callback_transaction_id)->toBe('ORIGINAL_GP_TRX');
});

it('processes Apple Pay payment callback successfully', function () {
    $reference = ApplePayReference::factory()->create([
        'reference' => 'AP-123456789',
        'value' => 450.00,
        'state' => 0,
    ]);

    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 450.00,
        'canal' => 'demo',
        'referencia' => 'AP-123456789',
        'transacao' => 'TRX-AP-789456',
        'identificador' => 5,
        'mp' => 'AP:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:14:00:00',
        'entidade' => '11249',
        'comissao' => 2.50,
        'local' => 'ONLINE',
    ]));

    $response->assertStatus(200)
        ->assertJson(['response' => 'Success']);

    $reference->refresh();
    expect($reference->state)->toBe(1)
        ->and($reference->callback_transaction_id)->toBe('TRX-AP-789456');

    Event::assertDispatched(CallbackReceived::class);
    Event::assertDispatched(ApplePayReferencePaid::class);
});

it('returns 404 when Apple Pay reference not found', function () {
    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 450.00,
        'canal' => 'demo',
        'referencia' => 'AP-999999999',
        'transacao' => 'TRX-AP-123',
        'identificador' => 5,
        'mp' => 'AP:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:14:00:00',
        'entidade' => '11249',
        'comissao' => 2.50,
        'local' => 'ONLINE',
    ]));

    $response->assertStatus(404)
        ->assertJson(['response' => 'No pending reference found']);

    Event::assertDispatched(CallbackReceived::class);
});

it('does not update already paid Apple Pay reference', function () {
    $reference = ApplePayReference::factory()->paid()->create([
        'reference' => 'AP-123456789',
        'value' => 450.00,
        'callback_transaction_id' => 'ORIGINAL_AP_TRX',
    ]);

    $response = $this->get('/eupago/callback?' . http_build_query([
        'valor' => 450.00,
        'canal' => 'demo',
        'referencia' => 'AP-123456789',
        'transacao' => 'NEW_AP_TRX',
        'identificador' => 5,
        'mp' => 'AP:PT',
        'chave_api' => 'test-api-key',
        'data' => '2024-01-01:14:00:00',
        'entidade' => '11249',
        'comissao' => 2.50,
        'local' => 'ONLINE',
    ]));

    $response->assertStatus(404);

    $reference->refresh();
    expect($reference->callback_transaction_id)->toBe('ORIGINAL_AP_TRX');
});
