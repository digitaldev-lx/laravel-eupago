<?php

declare(strict_types=1);

use DigitaldevLx\LaravelEupago\Payouts\PayoutTransaction;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('lists payout transactions successfully', function () {
    $mockResponse = json_encode([
        [
            'trid' => 'TXN-001',
            'date' => '2024-01-15',
            'amount' => 150.00,
            'payment_method' => 'MB',
            'status' => 'settled',
        ],
        [
            'trid' => 'TXN-002',
            'date' => '2024-01-15',
            'amount' => 230.50,
            'payment_method' => 'MBWAY',
            'status' => 'settled',
        ],
        [
            'trid' => 'TXN-003',
            'date' => '2024-01-16',
            'amount' => 500.00,
            'payment_method' => 'CC',
            'status' => 'settled',
        ],
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $payoutTransaction = new class('2024-01-15', '2024-01-16', 'test-bearer-token') extends PayoutTransaction
    {
        public function listWithClient(Client $client)
        {
            $response = $client->get(self::URI, $this->getParams());
            $transactionsData = json_decode($response->getBody()->getContents(), true);

            if (isset($transactionsData['error'])) {
                $errorMessage = $transactionsData['message'] ?? $transactionsData['error'] ?? 'Unknown error';
                $errorCode = $transactionsData['code'] ?? 'error';
                $this->addError($errorCode, $errorMessage);

                return ['success' => false, 'errors' => $this->errors];
            }

            return ['success' => true, 'transactions' => $transactionsData];
        }

        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $result = $payoutTransaction->listWithClient($client);

    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['transactions'])->toBeArray()
        ->and($result['transactions'])->toHaveCount(3)
        ->and($result['transactions'][0]['trid'])->toBe('TXN-001')
        ->and($result['transactions'][1]['amount'])->toBe(230.50)
        ->and($result['transactions'][2]['payment_method'])->toBe('CC');
});

it('handles transaction listing failure with invalid token', function () {
    $mockResponse = json_encode([
        'error' => 'Bearer Token Invalid',
        'message' => 'Token is invalid',
        'code' => 'INVALID_TOKEN',
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $payoutTransaction = new class('2024-01-15', '2024-01-16', 'invalid-bearer-token') extends PayoutTransaction
    {
        public function listWithClient(Client $client)
        {
            $response = $client->get(self::URI, $this->getParams());
            $transactionsData = json_decode($response->getBody()->getContents(), true);

            if (isset($transactionsData['error'])) {
                $errorMessage = $transactionsData['message'] ?? $transactionsData['error'] ?? 'Unknown error';
                $errorCode = $transactionsData['code'] ?? 'error';
                $this->addError($errorCode, $errorMessage);

                return ['success' => false, 'errors' => $this->errors];
            }

            return ['success' => true, 'transactions' => $transactionsData];
        }

        public function hasErrors(): bool
        {
            return parent::hasErrors();
        }

        public function getErrors(): array
        {
            return parent::getErrors();
        }

        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $result = $payoutTransaction->listWithClient($client);

    expect($payoutTransaction->hasErrors())->toBeTrue()
        ->and($payoutTransaction->getErrors())->toHaveKey('INVALID_TOKEN')
        ->and($payoutTransaction->getErrors()['INVALID_TOKEN'])->toBe('Token is invalid')
        ->and($result['success'])->toBeFalse();
});

it('builds correct parameters for API request', function () {
    $payoutTransaction = new class('2024-01-01', '2024-01-31', 'test-bearer-token-456') extends PayoutTransaction
    {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $payoutTransaction->getParams();

    expect($params)->toHaveKeys(['headers', 'query'])
        ->and($params['headers'])->toMatchArray([
            'Authorization' => 'Bearer test-bearer-token-456',
            'Content-Type' => 'application/json',
        ])
        ->and($params['query'])->toMatchArray([
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);
});

it('handles empty transactions list', function () {
    $mockResponse = json_encode([]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $payoutTransaction = new class('2024-01-15', '2024-01-16', 'test-bearer-token') extends PayoutTransaction
    {
        public function listWithClient(Client $client)
        {
            $response = $client->get(self::URI, $this->getParams());
            $transactionsData = json_decode($response->getBody()->getContents(), true);

            if (isset($transactionsData['error'])) {
                $errorMessage = $transactionsData['message'] ?? $transactionsData['error'] ?? 'Unknown error';
                $errorCode = $transactionsData['code'] ?? 'error';
                $this->addError($errorCode, $errorMessage);

                return ['success' => false, 'errors' => $this->errors];
            }

            return ['success' => true, 'transactions' => $transactionsData];
        }
    };

    $result = $payoutTransaction->listWithClient($client);

    expect($result['success'])->toBeTrue()
        ->and($result['transactions'])->toBeArray()
        ->and($result['transactions'])->toHaveCount(0);
});

it('uses same date for single day query', function () {
    $payoutTransaction = new class('2024-01-15', '2024-01-15', 'test-bearer-token') extends PayoutTransaction
    {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $payoutTransaction->getParams();

    expect($params['query']['start_date'])->toBe('2024-01-15')
        ->and($params['query']['end_date'])->toBe('2024-01-15');
});

it('handles different payment methods in transactions', function () {
    $mockResponse = json_encode([
        ['trid' => 'TXN-MB', 'payment_method' => 'MB'],
        ['trid' => 'TXN-MBWAY', 'payment_method' => 'MBWAY'],
        ['trid' => 'TXN-CC', 'payment_method' => 'CC'],
        ['trid' => 'TXN-GP', 'payment_method' => 'GP'],
        ['trid' => 'TXN-AP', 'payment_method' => 'AP'],
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $payoutTransaction = new class('2024-01-01', '2024-01-31', 'test-bearer-token') extends PayoutTransaction
    {
        public function listWithClient(Client $client)
        {
            $response = $client->get(self::URI, $this->getParams());
            $transactionsData = json_decode($response->getBody()->getContents(), true);

            if (isset($transactionsData['error'])) {
                $errorMessage = $transactionsData['message'] ?? $transactionsData['error'] ?? 'Unknown error';
                $errorCode = $transactionsData['code'] ?? 'error';
                $this->addError($errorCode, $errorMessage);

                return ['success' => false, 'errors' => $this->errors];
            }

            return ['success' => true, 'transactions' => $transactionsData];
        }
    };

    $result = $payoutTransaction->listWithClient($client);

    expect($result['success'])->toBeTrue()
        ->and($result['transactions'])->toHaveCount(5)
        ->and(array_column($result['transactions'], 'payment_method'))
        ->toContain('MB', 'MBWAY', 'CC', 'GP', 'AP');
});
