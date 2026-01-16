<?php

use DigitaldevLx\LaravelEupago\Models\GooglePayReference;
use Illuminate\Database\Eloquent\Model;

it('can be instantiated', function () {
    $reference = new GooglePayReference();

    expect($reference)->toBeInstanceOf(Model::class);
});

it('has correct fillable attributes', function () {
    $reference = new GooglePayReference();

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
    $reference = GooglePayReference::factory()->create();

    expect($reference)->toBeInstanceOf(GooglePayReference::class)
        ->and($reference->id)->not->toBeNull()
        ->and($reference->transaction_id)->not->toBeNull()
        ->and($reference->reference)->not->toBeNull()
        ->and($reference->value)->toBeFloat()
        ->and($reference->redirect_url)->not->toBeNull()
        ->and($reference->customer_email)->not->toBeNull()
        ->and($reference->state)->toBe(0);
});

it('can create a paid reference using factory', function () {
    $reference = GooglePayReference::factory()->paid()->create();

    expect($reference->state)->toBe(1)
        ->and($reference->callback_transaction_id)->not->toBeNull();
});

it('has paid scope that filters paid references', function () {
    GooglePayReference::factory()->create(['state' => 0]);
    GooglePayReference::factory()->create(['state' => 1]);
    GooglePayReference::factory()->create(['state' => 1]);

    $paidReferences = GooglePayReference::paid()->get();

    expect($paidReferences)->toHaveCount(2);
    expect($paidReferences->every(fn($ref) => $ref->state === 1))->toBeTrue();
});

it('has polymorphic googlepayable relationship', function () {
    $reference = GooglePayReference::factory()->make([
        'googlepayable_type' => null,
        'googlepayable_id' => null,
    ]);

    expect($reference->googlepayable())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class);
});

it('casts dates correctly', function () {
    $reference = GooglePayReference::factory()->create();

    $reference->refresh();

    expect($reference->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($reference->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('can store redirect URL', function () {
    $redirectUrl = 'https://sandbox.eupago.pt/api/extern/googlepay/form/abc123def456';

    $reference = GooglePayReference::factory()->create([
        'redirect_url' => $redirectUrl,
    ]);

    expect($reference->redirect_url)->toBe($redirectUrl);
});

it('can store customer details', function () {
    $email = 'customer@example.com';
    $firstName = 'John';
    $lastName = 'Doe';
    $countryCode = 'PT';

    $reference = GooglePayReference::factory()->create([
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
