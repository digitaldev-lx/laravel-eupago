<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Traits;

use DigitaldevLx\LaravelEupago\ApplePay\ApplePay;
use DigitaldevLx\LaravelEupago\Models\ApplePayReference;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Applepayable
{
    /** @return MorphMany<ApplePayReference, $this> */
    public function applePayReferences(): MorphMany
    {
        return $this->morphMany(ApplePayReference::class, 'applepayable');
    }

    /**
     * @return array<string|int, string>|ApplePayReference
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
        ?int $minutesFormUp = null,
    ): array|ApplePayReference {
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

        $applePayReferenceData = $applePay->create();

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
