<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\CreditCard;

use DigitaldevLx\LaravelEupago\EuPago;
use DigitaldevLx\LaravelEupago\Events\CreditCardRecurrenceAuthorizationCreated;
use DigitaldevLx\LaravelEupago\Events\CreditCardRecurrenceAuthorizationFailed;
use GuzzleHttp\Client;

class CreditCardRecurrence extends EuPago
{
    public const string URI = '/api/v1.02/creditcard/subscription';

    public function __construct(
        protected readonly string $identifier,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function create(): array
    {
        $client = new Client(['base_uri' => $this->getBaseUri()]);

        $response = $client->post(self::URI, $this->getParams());

        /** @var array<string, mixed> $responseData */
        $responseData = json_decode($response->getBody()->getContents(), true);

        if (! isset($responseData['transactionStatus']) || $responseData['transactionStatus'] !== 'Success') {
            $errorMessage = $responseData['message'] ?? 'Unknown error';
            $this->addError('error', (string) $errorMessage);

            event(new CreditCardRecurrenceAuthorizationFailed(
                $this->errors,
                [
                    'identifier' => $this->identifier,
                ]
            ));

            return [
                'success' => false,
                'errors' => $this->errors,
            ];
        }

        $mappedData = $this->mappedResponseKeys($responseData);
        event(new CreditCardRecurrenceAuthorizationCreated($mappedData));

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
            'status_subs' => $responseData['statusSubs'] ?? null,
            'subscription_id' => $responseData['subscriptionID'] ?? null,
            'reference_subs' => $responseData['referenceSubs'] ?? null,
            'redirect_url' => $responseData['redirectUrl'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getParams(): array
    {
        return [
            'headers' => [
                'ApiKey' => config('eupago.api_key'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'payment' => [
                    'identifier' => $this->identifier,
                ],
            ],
        ];
    }
}
