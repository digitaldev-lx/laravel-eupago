<?php

namespace DigitaldevLx\LaravelEupago\Traits;

use DigitaldevLx\LaravelEupago\CreditCard\CreditCard;
use DigitaldevLx\LaravelEupago\Models\CreditCardReference;

trait Creditcardable
{
    /**
     * Get all of the model's Credit Card references.
     */
    public function creditCardReferences()
    {
        return $this->morphMany(CreditCardReference::class, 'creditcardable');
    }

    /**
     * Creates a Credit Card reference.
     *
     * @param float $value
     * @param string $identifier
     * @param string $successUrl
     * @param string $failUrl
     * @param string $backUrl
     * @param string $customerEmail
     * @param string $lang
     * @param string $currency
     * @param bool $customerNotify
     * @param int|null $minutesFormUp
     * @return array|CreditCardReference
     * @throws \Exception
     */
    public function createCreditCardReference(
        float $value,
        string $identifier,
        string $successUrl,
        string $failUrl,
        string $backUrl,
        string $customerEmail,
        string $lang = 'PT',
        string $currency = 'EUR',
        bool $customerNotify = true,
        ?int $minutesFormUp = null
    ) {
        $creditCard = new CreditCard(
            $value,
            $identifier,
            $successUrl,
            $failUrl,
            $backUrl,
            $customerEmail,
            $lang,
            $currency,
            $customerNotify,
            $minutesFormUp
        );

        try {
            $creditCardReferenceData = $creditCard->create();
        } catch (\Exception $e) {
            throw $e;
        }

        if ($creditCard->hasErrors()) {
            return $creditCard->getErrors();
        }

        return $this->creditCardReferences()->create([
            'transaction_id' => $creditCardReferenceData['transaction_id'],
            'reference' => $creditCardReferenceData['reference'],
            'value' => $value,
            'redirect_url' => $creditCardReferenceData['redirect_url'],
            'state' => 0,
            'customer_email' => $customerEmail,
        ]);
    }
}
