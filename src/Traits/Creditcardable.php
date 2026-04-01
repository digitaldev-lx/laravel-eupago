<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Traits;

use DigitaldevLx\LaravelEupago\CreditCard\CreditCard;
use DigitaldevLx\LaravelEupago\Models\CreditCardReference;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Creditcardable
{
    /** @return MorphMany<CreditCardReference, $this> */
    public function creditCardReferences(): MorphMany
    {
        return $this->morphMany(CreditCardReference::class, 'creditcardable');
    }

    /**
     * @return array<string|int, string>|CreditCardReference
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
        ?int $minutesFormUp = null,
    ): array|CreditCardReference {
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

        $creditCardReferenceData = $creditCard->create();

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
