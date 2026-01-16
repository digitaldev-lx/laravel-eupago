<?php

use DigitaldevLx\LaravelEupago\Models\CreditCardRecurrenceAuthorization;
use DigitaldevLx\LaravelEupago\Traits\Creditcardrecurrable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    $this->testModelClass = new class extends Model {
        use Creditcardrecurrable;

        protected $table = 'test_models';
        protected $guarded = [];
    };
});

afterEach(function () {
    Schema::dropIfExists('test_models');
});

it('has creditCardRecurrenceAuthorizations relationship', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    expect($model->creditCardRecurrenceAuthorizations())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
});

it('can retrieve credit card recurrence authorizations through relationship', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    $authorization = CreditCardRecurrenceAuthorization::factory()->create([
        'creditcardrecurrable_type' => get_class($model),
        'creditcardrecurrable_id' => $model->id,
    ]);

    expect($model->creditCardRecurrenceAuthorizations)->toHaveCount(1)
        ->and($model->creditCardRecurrenceAuthorizations->first()->id)->toBe($authorization->id);
});

it('can have multiple credit card recurrence authorizations', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    CreditCardRecurrenceAuthorization::factory()->count(3)->create([
        'creditcardrecurrable_type' => get_class($model),
        'creditcardrecurrable_id' => $model->id,
    ]);

    expect($model->creditCardRecurrenceAuthorizations)->toHaveCount(3);
});
