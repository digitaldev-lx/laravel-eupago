<?php

declare(strict_types=1);

use DigitaldevLx\LaravelEupago\Models\ApplePayReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

it('can be instantiated', function () {
    $reference = new ApplePayReference;

    expect($reference)->toBeInstanceOf(Model::class);
});

it('has correct fillable attributes', function () {
    $reference = new ApplePayReference;

    expect($reference->getFillable())->toContain(
        'transaction_id',
        'reference',
        'value',
        'redirect_url',
        'state',
        'callback_transaction_id',
        'customer_email',
        'customer_first_name',
        'customer_last_name',
        'customer_country_code'
    );
});

it('can create a reference using factory', function () {
    $reference = ApplePayReference::factory()->create();

    expect($reference)->toBeInstanceOf(ApplePayReference::class)
        ->and($reference->id)->not->toBeNull()
        ->and($reference->transaction_id)->not->toBeNull()
        ->and($reference->reference)->not->toBeNull()
        ->and($reference->value)->toBeFloat()
        ->and($reference->redirect_url)->not->toBeNull()
        ->and($reference->customer_email)->not->toBeNull()
        ->and($reference->state)->toBe(0);
});

it('can create a paid reference using factory', function () {
    $reference = ApplePayReference::factory()->paid()->create();

    expect($reference->state)->toBe(1)
        ->and($reference->callback_transaction_id)->not->toBeNull();
});

it('has paid scope that filters paid references', function () {
    ApplePayReference::factory()->create(['state' => 0]);
    ApplePayReference::factory()->create(['state' => 1]);
    ApplePayReference::factory()->create(['state' => 1]);

    $paidReferences = ApplePayReference::paid()->get();

    expect($paidReferences)->toHaveCount(2);
    expect($paidReferences->every(fn ($ref) => $ref->state === 1))->toBeTrue();
});

it('has polymorphic applepayable relationship', function () {
    $reference = ApplePayReference::factory()->make([
        'applepayable_type' => null,
        'applepayable_id' => null,
    ]);

    expect($reference->applepayable())->toBeInstanceOf(MorphTo::class);
});

it('casts dates correctly', function () {
    $reference = ApplePayReference::factory()->create();

    $reference->refresh();

    expect($reference->created_at)->toBeInstanceOf(Carbon::class)
        ->and($reference->updated_at)->toBeInstanceOf(Carbon::class);
});

it('can store redirect URL', function () {
    $redirectUrl = 'https://sandbox.eupago.pt/api/extern/euapplepay/form/abc123def456';

    $reference = ApplePayReference::factory()->create([
        'redirect_url' => $redirectUrl,
    ]);

    expect($reference->redirect_url)->toBe($redirectUrl);
});

it('can store customer details', function () {
    $email = 'customer@example.com';
    $firstName = 'John';
    $lastName = 'Doe';
    $countryCode = 'PT';

    $reference = ApplePayReference::factory()->create([
        'customer_email' => $email,
        'customer_first_name' => $firstName,
        'customer_last_name' => $lastName,
        'customer_country_code' => $countryCode,
    ]);

    expect($reference->customer_email)->toBe($email)
        ->and($reference->customer_first_name)->toBe($firstName)
        ->and($reference->customer_last_name)->toBe($lastName)
        ->and($reference->customer_country_code)->toBe($countryCode);
});
