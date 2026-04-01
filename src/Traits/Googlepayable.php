<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Traits;

use DigitaldevLx\LaravelEupago\GooglePay\GooglePay;
use DigitaldevLx\LaravelEupago\Models\GooglePayReference;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Googlepayable
{
    /** @return MorphMany<GooglePayReference, $this> */
    public function googlePayReferences(): MorphMany
    {
        return $this->morphMany(GooglePayReference::class, 'googlepayable');
    }

    /**
     * @return array<string|int, string>|GooglePayReference
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
        ?int $minutesFormUp = null,
    ): array|GooglePayReference {
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

        $googlePayReferenceData = $googlePay->create();

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
