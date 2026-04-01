<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Database\Factories;

use DigitaldevLx\LaravelEupago\Models\MbReference;
use Illuminate\Database\Eloquent\Factories\Factory;

class MbReferenceFactory extends Factory
{
    protected $model = MbReference::class;

    public function definition(): array
    {
        $value = $this->faker->randomFloat(2, 10, 1000);

        return [
            'entity' => '11249',
            'reference' => $this->faker->unique()->numerify('#########'),
            'value' => $value,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'min_value' => $value,
            'max_value' => $value,
            'state' => 0,
            'transaction_id' => null,
            'mbable_type' => 'App\\Models\\Order',
            'mbable_id' => $this->faker->numberBetween(1, 1000),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 1,
            'transaction_id' => $this->faker->unique()->numerify('TRX##########'),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_date' => now()->subDays(14)->format('Y-m-d'),
            'end_date' => now()->subDays(7)->format('Y-m-d'),
            'state' => 0,
        ]);
    }
}
