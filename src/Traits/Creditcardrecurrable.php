<?php

namespace DigitaldevLx\LaravelEupago\Traits;

use DigitaldevLx\LaravelEupago\CreditCard\CreditCardRecurrence;
use DigitaldevLx\LaravelEupago\CreditCard\CreditCardRecurringPayment;
use DigitaldevLx\LaravelEupago\Models\CreditCardRecurrenceAuthorization;

trait Creditcardrecurrable
{
    /**
     * Get all of the model's Credit Card Recurrence Authorizations.
     */
    public function creditCardRecurrenceAuthorizations()
    {
        return $this->morphMany(CreditCardRecurrenceAuthorization::class, 'creditcardrecurrable');
    }

    /**
     * Creates a Credit Card Recurrence Authorization.
     *
     * @param string $identifier
     * @return array|CreditCardRecurrenceAuthorization
     * @throws \Exception
     */
    public function createCreditCardRecurrenceAuthorization(string $identifier)
    {
        $recurrence = new CreditCardRecurrence($identifier);

        try {
            $authorizationData = $recurrence->create();
        } catch (\Exception $e) {
            throw $e;
        }

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
     * Creates a recurring payment using an existing authorization.
     *
     * @param CreditCardRecurrenceAuthorization $authorization
     * @param float $value
     * @param string $identifier
     * @param string $currency
     * @param string|null $customerEmail
     * @param bool $customerNotify
     * @return array|\DigitaldevLx\LaravelEupago\Models\CreditCardRecurringPayment
     * @throws \Exception
     */
    public function createRecurringPayment(
        CreditCardRecurrenceAuthorization $authorization,
        float $value,
        string $identifier,
        string $currency = 'EUR',
        ?string $customerEmail = null,
        bool $customerNotify = false
    ) {
        $recurringPayment = new CreditCardRecurringPayment(
            $authorization->subscription_id,
            $value,
            $identifier,
            $currency,
            $customerEmail,
            $customerNotify
        );

        try {
            $paymentData = $recurringPayment->create();
        } catch (\Exception $e) {
            throw $e;
        }

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
