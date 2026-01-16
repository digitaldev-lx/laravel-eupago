<?php

namespace DigitaldevLx\LaravelEupago\Database\Factories;

use DigitaldevLx\LaravelEupago\Models\CreditCardReference;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditCardReferenceFactory extends Factory
{
    protected $model = CreditCardReference::class;

    public function definition(): array
    {
        $value = $this->faker->randomFloat(2, 10, 3999);

        return [
            'transaction_id' => $this->faker->unique()->uuid(),
            'reference' => $this->faker->unique()->numerify('#########'),
            'value' => $value,
            'redirect_url' => $this->faker->url(),
            'state' => 0,
            'callback_transaction_id' => null,
            'customer_email' => $this->faker->email(),
            'creditcardable_type' => 'App\\Models\\Order',
            'creditcardable_id' => $this->faker->numberBetween(1, 1000),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 1,
            'callback_transaction_id' => $this->faker->unique()->numerify('TRX##########'),
        ]);
    }
}
