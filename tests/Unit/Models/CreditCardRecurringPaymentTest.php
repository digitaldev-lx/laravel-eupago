<?php

use DigitaldevLx\LaravelEupago\Models\CreditCardRecurringPayment;
use DigitaldevLx\LaravelEupago\Models\CreditCardRecurrenceAuthorization;
use Illuminate\Database\Eloquent\Model;

it('can be instantiated', function () {
    $payment = new CreditCardRecurringPayment();

    expect($payment)->toBeInstanceOf(Model::class);
});

it('has correct fillable attributes', function () {
    $payment = new CreditCardRecurringPayment();

    expect($payment->getFillable())->toContain(
        'authorization_id',
        'transaction_id',
        'reference',
        'value',
        'status',
        'identifier',
        'message'
    );
});

it('can create a payment using factory', function () {
    $payment = CreditCardRecurringPayment::factory()->create();

    expect($payment)->toBeInstanceOf(CreditCardRecurringPayment::class)
        ->and($payment->id)->not->toBeNull()
        ->and($payment->authorization_id)->not->toBeNull()
        ->and($payment->transaction_id)->not->toBeNull()
        ->and($payment->reference)->not->toBeNull()
        ->and($payment->value)->toBeFloat()
        ->and($payment->identifier)->not->toBeNull()
        ->and($payment->status)->toBe('Paid');
});

it('can create a paid payment using factory', function () {
    $payment = CreditCardRecurringPayment::factory()->paid()->create();

    expect($payment->status)->toBe('Paid')
        ->and($payment->message)->toBe('Payment has been executed successfully.');
});

it('has paid scope that filters paid payments', function () {
    CreditCardRecurringPayment::factory()->create(['status' => 'Pending']);
    CreditCardRecurringPayment::factory()->create(['status' => 'Paid']);
    CreditCardRecurringPayment::factory()->create(['status' => 'Paid']);

    $paidPayments = CreditCardRecurringPayment::paid()->get();

    expect($paidPayments)->toHaveCount(2);
    expect($paidPayments->every(fn($payment) => $payment->status === 'Paid'))->toBeTrue();
});

it('has authorization relationship', function () {
    $payment = CreditCardRecurringPayment::factory()->make();

    expect($payment->authorization())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class);
});

it('can retrieve authorization through relationship', function () {
    $authorization = CreditCardRecurrenceAuthorization::factory()->create();
    $payment = CreditCardRecurringPayment::factory()->create([
        'authorization_id' => $authorization->id,
    ]);

    expect($payment->authorization)->not->toBeNull()
        ->and($payment->authorization->id)->toBe($authorization->id);
});

it('casts dates correctly', function () {
    $payment = CreditCardRecurringPayment::factory()->create();

    $payment->refresh();

    expect($payment->created_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and($payment->updated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('can store payment message', function () {
    $message = 'Payment has been executed successfully.';

    $payment = CreditCardRecurringPayment::factory()->create([
        'message' => $message,
    ]);

    expect($payment->message)->toBe($message);
});
