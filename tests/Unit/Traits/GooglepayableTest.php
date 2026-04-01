<?php

declare(strict_types=1);

use DigitaldevLx\LaravelEupago\Models\GooglePayReference;
use DigitaldevLx\LaravelEupago\Traits\Googlepayable;
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
        use Googlepayable;

        protected $table = 'test_models';

        protected $guarded = [];
    };
});

afterEach(function () {
    Schema::dropIfExists('test_models');
});

it('has googlePayReferences relationship', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    expect($model->googlePayReferences())->toBeInstanceOf(MorphMany::class);
});

it('can retrieve google pay references through relationship', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    $reference = GooglePayReference::factory()->create([
        'googlepayable_type' => get_class($model),
        'googlepayable_id' => $model->id,
    ]);

    expect($model->googlePayReferences)->toHaveCount(1)
        ->and($model->googlePayReferences->first()->id)->toBe($reference->id);
});

it('can have multiple google pay references', function () {
    $model = $this->testModelClass::create(['name' => 'Test']);

    GooglePayReference::factory()->count(3)->create([
        'googlepayable_type' => get_class($model),
        'googlepayable_id' => $model->id,
    ]);

    expect($model->googlePayReferences)->toHaveCount(3);
});
