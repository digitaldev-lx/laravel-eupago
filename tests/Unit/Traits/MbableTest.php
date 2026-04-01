<?php

declare(strict_types=1);

use DigitaldevLx\LaravelEupago\Models\MbReference;
use DigitaldevLx\LaravelEupago\Traits\Mbable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('test_models', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    $this->testModelClass = new class extends Model
    {
        use Mbable;

        protected $table = 'test_models';

        protected $guarded = [];
    };
});

afterEach(function () {
    Schema::dropIfExists('test_models');
});

it('has mbReferences relationship', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    expect($model->mbReferences())->toBeInstanceOf(MorphMany::class);
});

it('can retrieve mb references through relationship', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    $reference = MbReference::factory()->create([
        'mbable_type' => get_class($model),
        'mbable_id' => $model->id,
    ]);

    expect($model->mbReferences)->toHaveCount(1)
        ->and($model->mbReferences->first()->id)->toBe($reference->id);
});

it('can have multiple mb references', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    MbReference::factory()->count(3)->create([
        'mbable_type' => get_class($model),
        'mbable_id' => $model->id,
    ]);

    expect($model->mbReferences)->toHaveCount(3);
});
