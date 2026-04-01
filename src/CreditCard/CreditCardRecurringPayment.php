<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\CreditCard;

use DigitaldevLx\LaravelEupago\EuPago;
use DigitaldevLx\LaravelEupago\Events\CreditCardRecurringPaymentCreated;
use DigitaldevLx\LaravelEupago\Events\CreditCardRecurringPaymentFailed;
use GuzzleHttp\Client;

class CreditCardRecurringPayment extends EuPago
{
    public const string URI = '/api/v1.02/creditcard/payment/';

    public function __construct(
        protected readonly string $subscriptionId,
        protected readonly float $value,
        protected readonly string $identifier,
        protected readonly string $currency = 'EUR',
        protected readonly ?string $customerEmail = null,
        protected readonly bool $customerNotify = false,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function create(): array
    {
        $client = new Client(['base_uri' => $this->getBaseUri()]);

        $response = $client->post(self::URI.$this->subscriptionId, $this->getParams());

        /** @var array<string, mixed> $responseData */
        $responseData = json_decode($response->getBody()->getContents(), true);

        if (! isset($responseData['transactionStatus']) || $responseData['transactionStatus'] !== 'Success') {
            $errorMessage = $responseData['text'] ?? $responseData['message'] ?? 'Unknown error';
            $errorCode = $responseData['code'] ?? 'error';
            $this->addError((string) $errorCode, (string) $errorMessage);

            event(new CreditCardRecurringPaymentFailed(
                $this->errors,
                [
                    'subscription_id' => $this->subscriptionId,
                    'value' => $this->value,
                    'identifier' => $this->identifier,
                ]
            ));

            return [
                'success' => false,
                'errors' => $this->errors,
            ];
        }

        $mappedData = $this->mappedResponseKeys($responseData);
        event(new CreditCardRecurringPaymentCreated($mappedData));

        return $mappedData;
    }

    /**
     * @param  array<string, mixed>  $responseData
     * @return array<string, mixed>
     */
    protected function mappedResponseKeys(array $responseData): array
    {
        return [
            'success' => true,
            'transaction_status' => $responseData['transactionStatus'] ?? null,
            'status' => $responseData['status'] ?? null,
            'transaction_id' => $responseData['transactionID'] ?? null,
            'reference' => $responseData['reference'] ?? null,
            'message' => $responseData['message'] ?? null,
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
                ],
            ],
        ];

        if ($this->customerEmail && $this->customerNotify) {
            $params['json']['customer'] = [
                'notify' => $this->customerNotify,
                'email' => $this->customerEmail,
            ];
        }

        return $params;
    }
}
