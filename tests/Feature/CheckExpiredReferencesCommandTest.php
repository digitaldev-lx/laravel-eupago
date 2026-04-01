<?php

declare(strict_types=1);

use DigitaldevLx\LaravelEupago\Events\MBReferenceExpired;
use DigitaldevLx\LaravelEupago\Models\MbReference;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

it('finds and dispatches events for expired MB references', function () {
    // Create expired references
    MbReference::factory()->expired()->count(3)->create();

    // Create non-expired reference
    MbReference::factory()->create();

    // Create paid reference (should not be counted)
    MbReference::factory()->expired()->paid()->create();

    $this->artisan('eupago:check-expired')
        ->expectsOutput('Checking for expired references...')
        ->assertExitCode(0);

    Event::assertDispatched(MBReferenceExpired::class, 3);
});

it('handles case when no expired references exist', function () {
    MbReference::factory()->count(2)->create();

    $this->artisan('eupago:check-expired')
        ->expectsOutput('Found 0 expired MB references.')
        ->assertExitCode(0);

    Event::assertNotDispatched(MBReferenceExpired::class);
});

it('only dispatches events for unpaid expired references', function () {
    // Expired but paid (should not trigger event)
    MbReference::factory()->expired()->paid()->create();

    // Expired and unpaid (should trigger event)
    MbReference::factory()->expired()->create();

    $this->artisan('eupago:check-expired')
        ->assertExitCode(0);

    Event::assertDispatched(MBReferenceExpired::class, 1);
});

it('reports correct count of expired references', function () {
    MbReference::factory()->expired()->count(5)->create();

    $this->artisan('eupago:check-expired')
        ->expectsOutput('Found 5 expired MB references.')
        ->assertExitCode(0);
});
