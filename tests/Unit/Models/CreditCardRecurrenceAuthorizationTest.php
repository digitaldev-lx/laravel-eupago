<?php

declare(strict_types=1);

use DigitaldevLx\LaravelEupago\Models\CreditCardRecurrenceAuthorization;
use DigitaldevLx\LaravelEupago\Models\CreditCardRecurringPayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

it('can be instantiated', function () {
    $authorization = new CreditCardRecurrenceAuthorization;

    expect($authorization)->toBeInstanceOf(Model::class);
});

it('has correct fillable attributes', function () {
    $authorization = new CreditCardRecurrenceAuthorization;

    expect($authorization->getFillable())->toContain(
        'subscription_id',
        'reference_subs',
        'redirect_url',
        'status',
        'identifier'
    );
});

it('can create an authorization using factory', function () {
    $authorization = CreditCardRecurrenceAuthorization::factory()->create();

    expect($authorization)->toBeInstanceOf(CreditCardRecurrenceAuthorization::class)
        ->and($authorization->id)->not->toBeNull()
        ->and($authorization->subscription_id)->not->toBeNull()
        ->and($authorization->reference_subs)->not->toBeNull()
        ->and($authorization->redirect_url)->not->toBeNull()
        ->and($authorization->identifier)->not->toBeNull()
        ->and($authorization->status)->toBe('Pending');
});

it('can create an authorized authorization using factory', function () {
    $authorization = CreditCardRecurrenceAuthorization::factory()->authorized()->create();

    expect($authorization->status)->toBe('Authorized');
});

it('can create a pending authorization using factory', function () {
    $authorization = CreditCardRecurrenceAuthorization::factory()->pending()->create();

    expect($authorization->status)->toBe('Pending');
});

it('has authorized scope that filters authorized authorizations', function () {
    CreditCardRecurrenceAuthorization::factory()->create(['status' => 'Pending']);
    CreditCardRecurrenceAuthorization::factory()->create(['status' => 'Authorized']);
    CreditCardRecurrenceAuthorization::factory()->create(['status' => 'Authorized']);

    $authorizedAuthorizations = CreditCardRecurrenceAuthorization::authorized()->get();

    expect($authorizedAuthorizations)->toHaveCount(2);
    expect($authorizedAuthorizations->every(fn ($auth) => $auth->status === 'Authorized'))->toBeTrue();
});

it('has pending scope that filters pending authorizations', function () {
    CreditCardRecurrenceAuthorization::factory()->create(['status' => 'Pending']);
    CreditCardRecurrenceAuthorization::factory()->create(['status' => 'Pending']);
    CreditCardRecurrenceAuthorization::factory()->create(['status' => 'Authorized']);

    $pendingAuthorizations = CreditCardRecurrenceAuthorization::pending()->get();

    expect($pendingAuthorizations)->toHaveCount(2);
    expect($pendingAuthorizations->every(fn ($auth) => $auth->status === 'Pending'))->toBeTrue();
});

it('has polymorphic creditcardrecurrable relationship', function () {
    $authorization = CreditCardRecurrenceAuthorization::factory()->make([
        'creditcardrecurrable_type' => null,
        'creditcardrecurrable_id' => null,
    ]);

    expect($authorization->creditcardrecurrable())->toBeInstanceOf(MorphTo::class);
});

it('has recurring payments relationship', function () {
    $authorization = CreditCardRecurrenceAuthorization::factory()->create();

    expect($authorization->recurringPayments())->toBeInstanceOf(HasMany::class);
});

it('can retrieve recurring payments through relationship', function () {
    $authorization = CreditCardRecurrenceAuthorization::factory()->create();

    $payment = CreditCardRecurringPayment::factory()->create([
        'authorization_id' => $authorization->id,
    ]);

    expect($authorization->recurringPayments)->toHaveCount(1)
        ->and($authorization->recurringPayments->first()->id)->toBe($payment->id);
});

it('casts dates correctly', function () {
    $authorization = CreditCardRecurrenceAuthorization::factory()->create();

    $authorization->refresh();

    expect($authorization->created_at)->toBeInstanceOf(Carbon::class)
        ->and($authorization->updated_at)->toBeInstanceOf(Carbon::class);
});

it('can store redirect URL', function () {
    $redirectUrl = 'https://sandbox.eupago.pt/subscription/form?token=abc123def456';

    $authorization = CreditCardRecurrenceAuthorization::factory()->create([
        'redirect_url' => $redirectUrl,
    ]);

    expect($authorization->redirect_url)->toBe($redirectUrl);
});
