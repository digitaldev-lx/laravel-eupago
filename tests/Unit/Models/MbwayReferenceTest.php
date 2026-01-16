<?php

use DigitaldevLx\LaravelEupago\Models\MbwayReference;
use Illuminate\Database\Eloquent\Model;

it('can be instantiated', function () {
    $reference = new MbwayReference();

    expect($reference)->toBeInstanceOf(Model::class);
});

it('has correct fillable attributes', function () {
    $reference = new MbwayReference();

    expect($reference->getFillable())->toContain('reference', 'value', 'alias', 'state', 'transaction_id');
});

it('can create a reference using factory', function () {
    $reference = MbwayReference::factory()->create();

    expect($reference)->toBeInstanceOf(MbwayReference::class)
        ->and($reference->id)->not->toBeNull()
        ->and($reference->reference)->toBeInt()
        ->and($reference->alias)->toBeString()
        ->and($reference->value)->toBeFloat()
        ->and($reference->state)->toBe(0);
});

it('can create a paid reference using factory', function () {
    $reference = MbwayReference::factory()->paid()->create();

    expect($reference->state)->toBe(1)
        ->and($reference->transaction_id)->not->toBeNull();
});

it('has paid scope that filters paid references', function () {
    MbwayReference::factory()->create(['state' => 0]);
    MbwayReference::factory()->create(['state' => 1]);
    MbwayReference::factory()->create(['state' => 1]);

    $paidReferences = MbwayReference::paid()->get();

    expect($paidReferences)->toHaveCount(2);
    expect($paidReferences->every(fn($ref) => $ref->state === 1))->toBeTrue();
});

it('has polymorphic mbwayable relationship', function () {
    $reference = MbwayReference::factory()->make([
        'mbwayable_type' => null,
        'mbwayable_id' => null,
    ]);

    expect($reference->mbwayable())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphTo::class);
});
