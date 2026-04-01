<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Traits;

use DigitaldevLx\LaravelEupago\CreditCard\CreditCardRecurrence;
use DigitaldevLx\LaravelEupago\CreditCard\CreditCardRecurringPayment as CreditCardRecurringPaymentHandler;
use DigitaldevLx\LaravelEupago\Models\CreditCardRecurrenceAuthorization;
use DigitaldevLx\LaravelEupago\Models\CreditCardRecurringPayment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Creditcardrecurrable
{
    /** @return MorphMany<CreditCardRecurrenceAuthorization, $this> */
    public function creditCardRecurrenceAuthorizations(): MorphMany
    {
        return $this->morphMany(CreditCardRecurrenceAuthorization::class, 'creditcardrecurrable');
    }

    /**
     * @return array<string|int, string>|CreditCardRecurrenceAuthorization
     */
    public function createCreditCardRecurrenceAuthorization(string $identifier): array|CreditCardRecurrenceAuthorization
    {
        $recurrence = new CreditCardRecurrence($identifier);

        $authorizationData = $recurrence->create();

        if ($recurrence->hasErrors()) {
            return $recurrence->getErrors();
        }

        return $this->creditCardRecurrenceAuthorizations()->create([
            'subscription_id' => $authorizationData['subscription_id'],
            'reference_subs' => $authorizationData['reference_subs'],
            'redirect_url' => $authorizationData['redirect_url'],
            'status' => $authorizationData['status_subs'],
            'identifier' => $identifier,
        ]);
    }

    /**
     * @return array<string|int, string>|CreditCardRecurringPayment
     */
    public function createRecurringPayment(
        CreditCardRecurrenceAuthorization $authorization,
        float $value,
        string $identifier,
        string $currency = 'EUR',
        ?string $customerEmail = null,
        bool $customerNotify = false,
    ): array|CreditCardRecurringPayment {
        $recurringPayment = new CreditCardRecurringPaymentHandler(
            $authorization->subscription_id,
            $value,
            $identifier,
            $currency,
            $customerEmail,
            $customerNotify
        );

        $paymentData = $recurringPayment->create();

        if ($recurringPayment->hasErrors()) {
            return $recurringPayment->getErrors();
        }

        return $authorization->recurringPayments()->create([
            'transaction_id' => $paymentData['transaction_id'],
            'reference' => $paymentData['reference'],
            'value' => $value,
            'status' => $paymentData['status'],
            'identifier' => $identifier,
            'message' => $paymentData['message'],
        ]);
    }
}
