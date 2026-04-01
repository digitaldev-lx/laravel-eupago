<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Database\Factories;

use DigitaldevLx\LaravelEupago\Models\CreditCardRecurrenceAuthorization;
use DigitaldevLx\LaravelEupago\Models\CreditCardRecurringPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CreditCardRecurringPaymentFactory extends Factory
{
    protected $model = CreditCardRecurringPayment::class;

    public function definition(): array
    {
        $value = $this->faker->randomFloat(2, 10, 3999);

        return [
            'authorization_id' => CreditCardRecurrenceAuthorization::factory(),
            'transaction_id' => $this->faker->unique()->uuid(),
            'reference' => $this->faker->unique()->numerify('#########'),
            'value' => $value,
            'status' => 'Paid',
            'identifier' => $this->faker->unique()->numerify('PAY-####'),
            'message' => 'Payment has been executed successfully.',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Paid',
            'message' => 'Payment has been executed successfully.',
        ]);
    }
}
