<?php

namespace DigitaldevLx\LaravelEupago\Traits;

use DigitaldevLx\LaravelEupago\GooglePay\GooglePay;
use DigitaldevLx\LaravelEupago\Models\GooglePayReference;

trait Googlepayable
{
    /**
     * Get all of the model's Google Pay references.
     */
    public function googlePayReferences()
    {
        return $this->morphMany(GooglePayReference::class, 'googlepayable');
    }

    /**
     * Creates a Google Pay reference.
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
     * @return array|GooglePayReference
     * @throws \Exception
     */
    public function createGooglePayReference(
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
        $googlePay = new GooglePay(
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
            $googlePayReferenceData = $googlePay->create();
        } catch (\Exception $e) {
            throw $e;
        }

        if ($googlePay->hasErrors()) {
            return $googlePay->getErrors();
        }

        return $this->googlePayReferences()->create([
            'transaction_id' => $googlePayReferenceData['transaction_id'],
            'reference' => $googlePayReferenceData['reference'],
            'value' => $value,
            'redirect_url' => $googlePayReferenceData['redirect_url'],
            'state' => 0,
            'customer_email' => $customerEmail,
            'customer_first_name' => $customerFirstName,
            'customer_last_name' => $customerLastName,
            'customer_country_code' => $customerCountryCode,
        ]);
    }
}
