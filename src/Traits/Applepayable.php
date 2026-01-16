<?php

namespace DigitaldevLx\LaravelEupago\Traits;

use DigitaldevLx\LaravelEupago\ApplePay\ApplePay;
use DigitaldevLx\LaravelEupago\Models\ApplePayReference;

trait Applepayable
{
    /**
     * Get all of the model's Apple Pay references.
     */
    public function applePayReferences()
    {
        return $this->morphMany(ApplePayReference::class, 'applepayable');
    }

    /**
     * Creates an Apple Pay reference.
     *
     * @param float $value
     * @param string $identifier
     * @param string $successUrl
     * @param string $failUrl
     * @param string $backUrl
     * @param string $lang
     * @param string $currency
     * @param string|null $customerEmail
     * @param string|null $customerFirstName
     * @param string|null $customerLastName
     * @param string|null $customerCountryCode
     * @param bool $customerNotify
     * @param int|null $minutesFormUp
     * @return array|ApplePayReference
     * @throws \Exception
     */
    public function createApplePayReference(
        float $value,
        string $identifier,
        string $successUrl,
        string $failUrl,
        string $backUrl,
        string $lang = 'PT',
        string $currency = 'EUR',
        ?string $customerEmail = null,
        ?string $customerFirstName = null,
        ?string $customerLastName = null,
        ?string $customerCountryCode = null,
        bool $customerNotify = false,
        ?int $minutesFormUp = null
    ) {
        $applePay = new ApplePay(
            $value,
            $identifier,
            $successUrl,
            $failUrl,
            $backUrl,
            $lang,
            $currency,
            $customerEmail,
            $customerFirstName,
            $customerLastName,
            $customerCountryCode,
            $customerNotify,
            $minutesFormUp
        );

        try {
            $applePayReferenceData = $applePay->create();
        } catch (\Exception $e) {
            throw $e;
        }

        if ($applePay->hasErrors()) {
            return $applePay->getErrors();
        }

        return $this->applePayReferences()->create([
            'transaction_id' => $applePayReferenceData['transaction_id'],
            'reference' => $applePayReferenceData['reference'],
            'value' => $value,
            'redirect_url' => $applePayReferenceData['redirect_url'],
            'state' => 0,
            'customer_email' => $customerEmail,
            'customer_first_name' => $customerFirstName,
            'customer_last_name' => $customerLastName,
            'customer_country_code' => $customerCountryCode,
        ]);
    }
}
