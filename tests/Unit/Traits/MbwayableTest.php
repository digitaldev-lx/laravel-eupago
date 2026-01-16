<?php

use DigitaldevLx\LaravelEupago\Models\MbwayReference;
use DigitaldevLx\LaravelEupago\Traits\Mbwayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('test_mbway_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    $this->testModelClass = new class extends Model {
        use Mbwayable;

        protected $table = 'test_mbway_models';
        protected $guarded = [];
    };
});

afterEach(function () {
    Schema::dropIfExists('test_mbway_models');
});

it('has mbwayReferences relationship', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    expect($model->mbwayReferences())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\MorphMany::class);
});

it('can retrieve mbway references through relationship', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    $reference = MbwayReference::factory()->create([
        'mbwayable_type' => get_class($model),
        'mbwayable_id' => $model->id,
    ]);

    expect($model->mbwayReferences)->toHaveCount(1)
        ->and($model->mbwayReferences->first()->id)->toBe($reference->id);
});

it('can have multiple mbway references', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    MbwayReference::factory()->count(3)->create([
        'mbwayable_type' => get_class($model),
        'mbwayable_id' => $model->id,
    ]);

    expect($model->mbwayReferences)->toHaveCount(3);
});
