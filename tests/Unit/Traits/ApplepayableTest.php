<?php

use DigitaldevLx\LaravelEupago\Models\ApplePayReference;
use DigitaldevLx\LaravelEupago\Traits\Applepayable;
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
        use Applepayable;

        protected $table = 'test_models';
        protected $guarded = [];
    };
});

afterEach(function () {
    Schema::dropIfExists('test_models');
});

it('has applePayReferences relationship', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    expect($model->applePayReferences())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
});

it('can retrieve apple pay references through relationship', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    $reference = ApplePayReference::factory()->create([
        'applepayable_type' => get_class($model),
        'applepayable_id' => $model->id,
    ]);

    expect($model->applePayReferences)->toHaveCount(1)
        ->and($model->applePayReferences->first()->id)->toBe($reference->id);
});

it('can have multiple apple pay references', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    ApplePayReference::factory()->count(3)->create([
        'applepayable_type' => get_class($model),
        'applepayable_id' => $model->id,
    ]);

    expect($model->applePayReferences)->toHaveCount(3);
});
