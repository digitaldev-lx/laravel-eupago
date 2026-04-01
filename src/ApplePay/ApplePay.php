<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\ApplePay;

use DigitaldevLx\LaravelEupago\EuPago;
use DigitaldevLx\LaravelEupago\Events\ApplePayReferenceCreated;
use DigitaldevLx\LaravelEupago\Events\ApplePayReferenceCreationFailed;
use GuzzleHttp\Client;

class ApplePay extends EuPago
{
    public const string URI = '/api/v1.02/euapplepay/create';

    public function __construct(
        protected readonly float $value,
        protected readonly string $identifier,
        protected readonly string $successUrl,
        protected readonly string $failUrl,
        protected readonly string $backUrl,
        protected readonly string $lang = 'PT',
        protected readonly string $currency = 'EUR',
        protected readonly ?string $customerEmail = null,
        protected readonly ?string $customerFirstName = null,
        protected readonly ?string $customerLastName = null,
        protected readonly ?string $customerCountryCode = null,
        protected readonly bool $customerNotify = false,
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
            $errorMessage = $referenceData['text'] ?? $referenceData['message'] ?? 'Unknown error';
            $errorCode = $referenceData['code'] ?? 'error';
            $this->addError((string) $errorCode, (string) $errorMessage);

            event(new ApplePayReferenceCreationFailed(
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
        event(new ApplePayReferenceCreated($mappedData));

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
            ],
        ];

        if ($this->minutesFormUp !== null) {
            $params['json']['payment']['minutesFormUp'] = $this->minutesFormUp;
        }

        if ($this->customerEmail || $this->customerFirstName || $this->customerLastName || $this->customerCountryCode) {
            $params['json']['customer'] = [];

            if ($this->customerNotify && $this->customerEmail) {
                $params['json']['customer']['notify'] = $this->customerNotify;
            }

            if ($this->customerEmail) {
                $params['json']['customer']['email'] = $this->customerEmail;
            }

            if ($this->customerFirstName) {
                $params['json']['customer']['firstName'] = $this->customerFirstName;
            }

            if ($this->customerLastName) {
                $params['json']['customer']['lastName'] = $this->customerLastName;
            }

            if ($this->customerCountryCode) {
                $params['json']['customer']['countryCode'] = $this->customerCountryCode;
            }
        }

        return $params;
    }
}
