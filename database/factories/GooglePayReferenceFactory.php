<?php

namespace DigitaldevLx\LaravelEupago\Database\Factories;

use DigitaldevLx\LaravelEupago\Models\GooglePayReference;
use Illuminate\Database\Eloquent\Factories\Factory;

class GooglePayReferenceFactory extends Factory
{
    protected $model = GooglePayReference::class;

    public function definition(): array
    {
        $value = $this->faker->randomFloat(2, 10, 99999);

        return [
            'transaction_id' => $this->faker->unique()->uuid(),
            'reference' => $this->faker->unique()->numerify('#######'),
            'value' => $value,
            'redirect_url' => $this->faker->url(),
            'state' => 0,
            'callback_transaction_id' => null,
            'customer_email' => $this->faker->email(),
            'customer_first_name' => $this->faker->firstName(),
            'customer_last_name' => $this->faker->lastName(),
            'customer_country_code' => $this->faker->countryCode(),
            'googlepayable_type' => 'App\\Models\\Order',
            'googlepayable_id' => $this->faker->numberBetween(1, 1000),
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
