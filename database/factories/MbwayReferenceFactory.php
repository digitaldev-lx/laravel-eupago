<?php

namespace DigitaldevLx\LaravelEupago\Database\Factories;

use DigitaldevLx\LaravelEupago\Models\MbwayReference;
use Illuminate\Database\Eloquent\Factories\Factory;

class MbwayReferenceFactory extends Factory
{
    protected $model = MbwayReference::class;

    public function definition(): array
    {
        return [
            'reference' => $this->faker->unique()->numberBetween(1000000000, 9999999999),
            'value' => $this->faker->randomFloat(2, 10, 1000),
            'alias' => '+351' . $this->faker->numerify('#########'),
            'state' => 0,
            'transaction_id' => null,
            'mbwayable_type' => 'App\\Models\\Order',
            'mbwayable_id' => $this->faker->numberBetween(1, 1000),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 1,
            'transaction_id' => $this->faker->unique()->numerify('TRX##########'),
        ]);
    }
}
