<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Payouts;

use DigitaldevLx\LaravelEupago\EuPago;
use GuzzleHttp\Client;

class PayoutTransaction extends EuPago
{
    public const string URI = '/api/management/v1.02/payouts/transactions';

    public function __construct(
        protected readonly string $startDate,
        protected readonly string $endDate,
        protected readonly string $bearerToken,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function list(): array
    {
        $client = new Client(['base_uri' => $this->getBaseUri()]);

        $response = $client->get(self::URI, $this->getParams());

        /** @var array<string, mixed> $transactionsData */
        $transactionsData = json_decode($response->getBody()->getContents(), true);

        if (isset($transactionsData['error'])) {
            $errorMessage = $transactionsData['message'] ?? 'Unknown error';
            $errorCode = $transactionsData['code'] ?? 'error';
            $this->addError((string) $errorCode, (string) $errorMessage);

            return [
                'success' => false,
                'errors' => $this->errors,
            ];
        }

        return [
            'success' => true,
            'transactions' => $transactionsData,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getParams(): array
    {
        return [
            'headers' => [
                'Authorization' => 'Bearer '.$this->bearerToken,
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
            ],
        ];
    }
}
