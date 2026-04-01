<?php

declare(strict_types=1);

use DigitaldevLx\LaravelEupago\Models\MbReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

it('can be instantiated', function () {
    $reference = new MbReference;

    expect($reference)->toBeInstanceOf(Model::class);
});

it('has correct fillable attributes', function () {
    $reference = new MbReference;

    expect($reference->getFillable())->toContain('entity', 'reference', 'value', 'state', 'transaction_id');
});

it('can create a reference using factory', function () {
    $reference = MbReference::factory()->create();

    expect($reference)->toBeInstanceOf(MbReference::class)
        ->and($reference->id)->not->toBeNull()
        ->and($reference->entity)->toBe('11249')
        ->and($reference->reference)->not->toBeNull()
        ->and($reference->value)->toBeFloat()
        ->and($reference->state)->toBe(0);
});

it('can create a paid reference using factory', function () {
    $reference = MbReference::factory()->paid()->create();

    expect($reference->state)->toBe(1)
        ->and($reference->transaction_id)->not->toBeNull();
});

it('can create an expired reference using factory', function () {
    $reference = MbReference::factory()->expired()->create();

    expect($reference->state)->toBe(0)
        ->and($reference->end_date)->toBeLessThan(now());
});

it('has paid scope that filters paid references', function () {
    MbReference::factory()->create(['state' => 0]);
    MbReference::factory()->create(['state' => 1]);
    MbReference::factory()->create(['state' => 1]);

    $paidReferences = MbReference::paid()->get();

    expect($paidReferences)->toHaveCount(2);
    expect($paidReferences->every(fn ($ref) => $ref->state === 1))->toBeTrue();
});

it('has polymorphic mbable relationship', function () {
    $reference = MbReference::factory()->make([
        'mbable_type' => null,
        'mbable_id' => null,
    ]);

    expect($reference->mbable())->toBeInstanceOf(MorphTo::class);
});

it('casts dates correctly', function () {
    $reference = MbReference::factory()->create([
        'start_date' => '2024-01-01',
        'end_date' => '2024-01-07',
    ]);

    $reference->refresh();

    expect($reference->start_date)->toBeInstanceOf(Carbon::class)
        ->and($reference->end_date)->toBeInstanceOf(Carbon::class);
});
