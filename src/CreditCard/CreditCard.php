<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\CreditCard;

use DigitaldevLx\LaravelEupago\EuPago;
use DigitaldevLx\LaravelEupago\Events\CreditCardReferenceCreated;
use DigitaldevLx\LaravelEupago\Events\CreditCardReferenceCreationFailed;
use GuzzleHttp\Client;

class CreditCard extends EuPago
{
    public const string URI = '/api/v1.02/creditcard/create';

    public function __construct(
        protected readonly float $value,
        protected readonly string $identifier,
        protected readonly string $successUrl,
        protected readonly string $failUrl,
        protected readonly string $backUrl,
        protected readonly string $customerEmail,
        protected readonly string $lang = 'PT',
        protected readonly string $currency = 'EUR',
        protected readonly bool $customerNotify = true,
        protected readonly ?int $minutesFormUp = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function create(): array
    {
        $client = new Client(['base_uri' => $this->getBaseUri()]);

        $response = $client->post(self::URI, $this->getParams());

        /** @var array<string, mixed> $referenceData */
        $referenceData = json_decode($response->getBody()->getContents(), true);

        if (! isset($referenceData['transactionStatus']) || $referenceData['transactionStatus'] !== 'Success') {
            $errorMessage = $referenceData['message'] ?? 'Unknown error';
            $this->addError('error', (string) $errorMessage);

            event(new CreditCardReferenceCreationFailed(
                $this->errors,
                [
                    'value' => $this->value,
                    'identifier' => $this->identifier,
                    'customer_email' => $this->customerEmail,
                ]
            ));

            return [
                'success' => false,
                'errors' => $this->errors,
            ];
        }

        $mappedData = $this->mappedReferenceKeys($referenceData);
        event(new CreditCardReferenceCreated($mappedData));

        return $mappedData;
    }

    /**
     * @param  array<string, mixed>  $referenceData
     * @return array<string, mixed>
     */
    protected function mappedReferenceKeys(array $referenceData): array
    {
        return [
            'success' => true,
            'transaction_status' => $referenceData['transactionStatus'] ?? null,
            'transaction_id' => $referenceData['transactionID'] ?? null,
            'reference' => $referenceData['reference'] ?? null,
            'redirect_url' => $referenceData['redirectUrl'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getParams(): array
    {
        $params = [
            'headers' => [
                'ApiKey' => config('eupago.api_key'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'payment' => [
                    'identifier' => $this->identifier,
                    'amount' => [
                        'value' => $this->value,
                        'currency' => $this->currency,
                    ],
                    'successUrl' => $this->successUrl,
                    'failUrl' => $this->failUrl,
                    'backUrl' => $this->backUrl,
                    'lang' => $this->lang,
                ],
                'customer' => [
                    'notify' => $this->customerNotify,
                    'email' => $this->customerEmail,
                ],
            ],
        ];

        if ($this->minutesFormUp !== null) {
            $params['json']['payment']['minutesFormUp'] = $this->minutesFormUp;
        }

        return $params;
    }
}
