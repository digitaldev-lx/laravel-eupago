<?php

use DigitaldevLx\LaravelEupago\Models\CreditCardReference;
use DigitaldevLx\LaravelEupago\Traits\Creditcardable;
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
        use Creditcardable;

        protected $table = 'test_models';
        protected $guarded = [];
    };
});

afterEach(function () {
    Schema::dropIfExists('test_models');
});

it('has creditCardReferences relationship', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    expect($model->creditCardReferences())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
});

it('can retrieve credit card references through relationship', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    $reference = CreditCardReference::factory()->create([
        'creditcardable_type' => get_class($model),
        'creditcardable_id' => $model->id,
    ]);

    expect($model->creditCardReferences)->toHaveCount(1)
        ->and($model->creditCardReferences->first()->id)->toBe($reference->id);
});

it('can have multiple credit card references', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    CreditCardReference::factory()->count(3)->create([
        'creditcardable_type' => get_class($model),
        'creditcardable_id' => $model->id,
    ]);

    expect($model->creditCardReferences)->toHaveCount(3);
});
