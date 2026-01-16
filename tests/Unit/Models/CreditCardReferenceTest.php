<?php

use DigitaldevLx\LaravelEupago\Models\CreditCardReference;
use Illuminate\Database\Eloquent\Model;

it('can be instantiated', function () {
    $reference = new CreditCardReference();

    expect($reference)->toBeInstanceOf(Model::class);
});

it('has correct fillable attributes', function () {
    $reference = new CreditCardReference();

    expect($reference->getFillable())->toContain(
        'transaction_id',
        'reference',
        'value',
        'redirect_url',
        'state',
        'callback_transaction_id',
        'customer_email'
    );
});

it('can create a reference using factory', function () {
    $reference = CreditCardReference::factory()->create();

    expect($reference)->toBeInstanceOf(CreditCardReference::class)
        ->and($reference->id)->not->toBeNull()
        ->and($reference->transaction_id)->not->toBeNull()
        ->and($reference->reference)->not->toBeNull()
        ->and($reference->value)->toBeFloat()
        ->and($reference->redirect_url)->not->toBeNull()
        ->and($reference->customer_email)->not->toBeNull()
        ->and($reference->state)->toBe(0);
});

it('can create a paid reference using factory', function () {
    $reference = CreditCardReference::factory()->paid()->create();

    expect($reference->state)->toBe(1)
        ->and($reference->callback_transaction_id)->not->toBeNull();
});

it('has paid scope that filters paid references', function () {
    CreditCardReference::factory()->create(['state' => 0]);
    CreditCardReference::factory()->create(['state' => 1]);
    CreditCardReference::factory()->create(['state' => 1]);

    $paidReferences = CreditCardReference::paid()->get();

    expect($paidReferences)->toHaveCount(2);
    expect($paidReferences->every(fn($ref) => $ref->state === 1))->toBeTrue();
});

it('has polymorphic creditcardable relationship', function () {
    $reference = CreditCardReference::factory()->make([
        'creditcardable_type' => null,
        'creditcardable_id' => null,
    ]);

    expect($reference->creditcardable())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class);
});

it('casts dates correctly', function () {
    $reference = CreditCardReference::factory()->create();

    $reference->refresh();

    expect($reference->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($reference->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('can store redirect URL', function () {
    $redirectUrl = 'https://sandbox.eupago.pt/payment/form?token=abc123def456';

    $reference = CreditCardReference::factory()->create([
        'redirect_url' => $redirectUrl,
    ]);

    expect($reference->redirect_url)->toBe($redirectUrl);
});

it('can store customer email', function () {
    $email = 'customer@example.com';

    $reference = CreditCardReference::factory()->create([
        'customer_email' => $email,
    ]);

    expect($reference->customer_email)->toBe($email);
});
