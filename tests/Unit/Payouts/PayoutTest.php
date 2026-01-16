<?php

use DigitaldevLx\LaravelEupago\Payouts\Payout;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

it('lists payouts successfully', function () {
    $mockResponse = json_encode([
        [
            'id' => 'PAYOUT-123',
            'date' => '2024-01-15',
            'amount' => 1500.00,
            'status' => 'completed',
        ],
        [
            'id' => 'PAYOUT-124',
            'date' => '2024-01-16',
            'amount' => 2300.50,
            'status' => 'completed',
        ],
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $payout = new class(
        '2024-01-15',
        '2024-01-16',
        'test-bearer-token'
    ) extends Payout {
        public function listWithClient(Client $client)
        {
            $response = $client->get(self::URI, $this->getParams());
            $payoutsData = json_decode($response->getBody()->getContents(), true);

            if (isset($payoutsData['error'])) {
                $errorMessage = $payoutsData['message'] ?? $payoutsData['error'] ?? 'Unknown error';
                $errorCode = $payoutsData['code'] ?? 'error';
                $this->addError($errorCode, $errorMessage);

                return ['success' => false, 'errors' => $this->errors];
            }

            return ['success' => true, 'payouts' => $payoutsData];
        }

        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $result = $payout->listWithClient($client);

    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['payouts'])->toBeArray()
        ->and($result['payouts'])->toHaveCount(2)
        ->and($result['payouts'][0]['id'])->toBe('PAYOUT-123')
        ->and($result['payouts'][1]['amount'])->toBe(2300.50);
});

it('handles payout listing failure with invalid token', function () {
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

    $payout = new class(
        '2024-01-15',
        '2024-01-16',
        'invalid-bearer-token'
    ) extends Payout {
        public function listWithClient(Client $client)
        {
            $response = $client->get(self::URI, $this->getParams());
            $payoutsData = json_decode($response->getBody()->getContents(), true);

            if (isset($payoutsData['error'])) {
                $errorMessage = $payoutsData['message'] ?? $payoutsData['error'] ?? 'Unknown error';
                $errorCode = $payoutsData['code'] ?? 'error';
                $this->addError($errorCode, $errorMessage);

                return ['success' => false, 'errors' => $this->errors];
            }

            return ['success' => true, 'payouts' => $payoutsData];
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

    $result = $payout->listWithClient($client);

    expect($payout->hasErrors())->toBeTrue()
        ->and($payout->getErrors())->toHaveKey('INVALID_TOKEN')
        ->and($payout->getErrors()['INVALID_TOKEN'])->toBe('Token is invalid')
        ->and($result['success'])->toBeFalse();
});

it('builds correct parameters for API request', function () {
    $payout = new class(
        '2024-01-01',
        '2024-01-31',
        'test-bearer-token-123'
    ) extends Payout {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $payout->getParams();

    expect($params)->toHaveKeys(['headers', 'query'])
        ->and($params['headers'])->toMatchArray([
            'Authorization' => 'Bearer test-bearer-token-123',
            'Content-Type' => 'application/json',
        ])
        ->and($params['query'])->toMatchArray([
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);
});

it('handles empty payouts list', function () {
    $mockResponse = json_encode([]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $payout = new class(
        '2024-01-15',
        '2024-01-16',
        'test-bearer-token'
    ) extends Payout {
        public function listWithClient(Client $client)
        {
            $response = $client->get(self::URI, $this->getParams());
            $payoutsData = json_decode($response->getBody()->getContents(), true);

            if (isset($payoutsData['error'])) {
                $errorMessage = $payoutsData['message'] ?? $payoutsData['error'] ?? 'Unknown error';
                $errorCode = $payoutsData['code'] ?? 'error';
                $this->addError($errorCode, $errorMessage);

                return ['success' => false, 'errors' => $this->errors];
            }

            return ['success' => true, 'payouts' => $payoutsData];
        }
    };

    $result = $payout->listWithClient($client);

    expect($result['success'])->toBeTrue()
        ->and($result['payouts'])->toBeArray()
        ->and($result['payouts'])->toHaveCount(0);
});

it('uses same date for single day query', function () {
    $payout = new class(
        '2024-01-15',
        '2024-01-15',
        'test-bearer-token'
    ) extends Payout {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $payout->getParams();

    expect($params['query']['start_date'])->toBe('2024-01-15')
        ->and($params['query']['end_date'])->toBe('2024-01-15');
});
