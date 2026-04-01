<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\Payouts;

use DigitaldevLx\LaravelEupago\EuPago;
use GuzzleHttp\Client;

class Payout extends EuPago
{
    public const string URI = '/api/management/v1.02/payouts';

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

        /** @var array<string, mixed> $payoutsData */
        $payoutsData = json_decode($response->getBody()->getContents(), true);

        if (isset($payoutsData['error'])) {
            $errorMessage = $payoutsData['message'] ?? 'Unknown error';
            $errorCode = $payoutsData['code'] ?? 'error';
            $this->addError((string) $errorCode, (string) $errorMessage);

            return [
                'success' => false,
                'errors' => $this->errors,
            ];
        }

        return [
            'success' => true,
            'payouts' => $payoutsData,
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
