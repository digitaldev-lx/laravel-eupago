<?php

namespace DigitaldevLx\LaravelEupago\Database\Factories;

use DigitaldevLx\LaravelEupago\Models\CreditCardRecurrenceAuthorization;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditCardRecurrenceAuthorizationFactory extends Factory
{
    protected $model = CreditCardRecurrenceAuthorization::class;

    public function definition(): array
    {
        return [
            'subscription_id' => $this->faker->unique()->numerify('SUB-##########'),
            'reference_subs' => $this->faker->unique()->numerify('#########'),
            'redirect_url' => $this->faker->url(),
            'status' => 'Pending',
            'identifier' => $this->faker->unique()->numerify('IDENT-####'),
            'creditcardrecurrable_type' => 'App\\Models\\Customer',
            'creditcardrecurrable_id' => $this->faker->numberBetween(1, 1000),
        ];
    }

    public function authorized(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Authorized',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Pending',
        ]);
    }
}
