<?php

use DigitaldevLx\LaravelEupago\Events\ApplePayReferenceCreated;
use DigitaldevLx\LaravelEupago\Events\ApplePayReferenceCreationFailed;
use DigitaldevLx\LaravelEupago\ApplePay\ApplePay;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

it('creates Apple Pay reference successfully', function () {
    $mockResponse = json_encode([
        'transactionStatus' => 'Success',
        'transactionID' => 'fc4740349d5b350eca08fa5e503fa0aa',
        'reference' => '2914983',
        'redirectUrl' => 'https://sandbox.eupago.pt/api/extern/euapplepay/form/abc123',
    ]);

    $mock = new MockHandler([
        new Response(201, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $applePay = new class(
        100.00,
        'ORDER-1',
        'https://example.com/success',
        'https://example.com/fail',
        'https://example.com/back',
        'PT',
        'EUR',
        'test@example.com'
    ) extends ApplePay {
        public function createWithClient(Client $client)
        {
            $response = $client->post(self::URI, $this->getParams());
            $referenceData = json_decode($response->getBody()->getContents(), true);

            if (!isset($referenceData['transactionStatus']) || $referenceData['transactionStatus'] !== 'Success') {
                $errorMessage = $referenceData['text'] ?? $referenceData['message'] ?? 'Unknown error';
                $errorCode = $referenceData['code'] ?? 'error';
                $this->addError($errorCode, $errorMessage);
                event(new ApplePayReferenceCreationFailed($this->errors, []));

                return ['success' => false, 'errors' => $this->errors];
            }

            $mappedData = $this->mappedReferenceKeys($referenceData);
            event(new ApplePayReferenceCreated($mappedData));

            return $mappedData;
        }

        public function getParams(): array
        {
            return parent::getParams();
        }

        public function mappedReferenceKeys(array $data): array
        {
            return parent::mappedReferenceKeys($data);
        }
    };

    $result = $applePay->createWithClient($client);

    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['transaction_id'])->toBe('fc4740349d5b350eca08fa5e503fa0aa')
        ->and($result['reference'])->toBe('2914983')
        ->and($result['redirect_url'])->toBe('https://sandbox.eupago.pt/api/extern/euapplepay/form/abc123');

    Event::assertDispatched(ApplePayReferenceCreated::class);
});

it('handles Apple Pay reference creation failure', function () {
    $mockResponse = json_encode([
        'transactionStatus' => 'Rejected',
        'code' => 'APIKEY_MISSING',
        'text' => 'API Key was not available in the request',
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $applePay = new class(
        100.00,
        'ORDER-1',
        'https://example.com/success',
        'https://example.com/fail',
        'https://example.com/back'
    ) extends ApplePay {
        public function createWithClient(Client $client)
        {
            $response = $client->post(self::URI, $this->getParams());
            $referenceData = json_decode($response->getBody()->getContents(), true);

            if (!isset($referenceData['transactionStatus']) || $referenceData['transactionStatus'] !== 'Success') {
                $errorMessage = $referenceData['text'] ?? $referenceData['message'] ?? 'Unknown error';
                $errorCode = $referenceData['code'] ?? 'error';
                $this->addError($errorCode, $errorMessage);
                event(new ApplePayReferenceCreationFailed($this->errors, []));

                return ['success' => false, 'errors' => $this->errors];
            }

            return $this->mappedReferenceKeys($referenceData);
        }

        public function getParams(): array
        {
            return parent::getParams();
        }

        public function hasErrors(): bool
        {
            return parent::hasErrors();
        }

        public function getErrors(): array
        {
            return parent::getErrors();
        }
    };

    $result = $applePay->createWithClient($client);

    expect($applePay->hasErrors())->toBeTrue()
        ->and($applePay->getErrors())->toHaveKey('APIKEY_MISSING')
        ->and($result['success'])->toBeFalse();

    Event::assertDispatched(ApplePayReferenceCreationFailed::class);
});

it('builds correct parameters for API request with full customer data', function () {
    config(['eupago.api_key' => 'demo-f298-22a3-1cea-101']);

    $applePay = new class(
        150.00,
        'ORDER-456',
        'https://example.com/success',
        'https://example.com/fail',
        'https://example.com/back',
        'EN',
        'EUR',
        'customer@example.com',
        'John',
        'Doe',
        'PT',
        true,
        60
    ) extends ApplePay {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $applePay->getParams();

    expect($params)->toHaveKeys(['headers', 'json'])
        ->and($params['headers'])->toMatchArray([
            'ApiKey' => 'demo-f298-22a3-1cea-101',
            'Content-Type' => 'application/json',
        ])
        ->and($params['json']['payment'])->toMatchArray([
            'identifier' => 'ORDER-456',
            'amount' => [
                'value' => 150.00,
                'currency' => 'EUR',
            ],
            'successUrl' => 'https://example.com/success',
            'failUrl' => 'https://example.com/fail',
            'backUrl' => 'https://example.com/back',
            'lang' => 'EN',
            'minutesFormUp' => 60,
        ])
        ->and($params['json']['customer'])->toMatchArray([
            'notify' => true,
            'email' => 'customer@example.com',
            'firstName' => 'John',
            'lastName' => 'Doe',
            'countryCode' => 'PT',
        ]);
});

it('builds parameters without optional minutesFormUp', function () {
    config(['eupago.api_key' => 'demo-f298-22a3-1cea-101']);

    $applePay = new class(
        100.00,
        'ORDER-789',
        'https://example.com/success',
        'https://example.com/fail',
        'https://example.com/back'
    ) extends ApplePay {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $applePay->getParams();

    expect($params['json']['payment'])->not->toHaveKey('minutesFormUp');
});

it('builds parameters without customer data', function () {
    config(['eupago.api_key' => 'demo-f298-22a3-1cea-101']);

    $applePay = new class(
        100.00,
        'ORDER-789',
        'https://example.com/success',
        'https://example.com/fail',
        'https://example.com/back'
    ) extends ApplePay {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $applePay->getParams();

    expect($params['json'])->not->toHaveKey('customer');
});

it('maps API response keys correctly', function () {
    $applePay = new class(
        100.00,
        'ORDER-1',
        'https://example.com/success',
        'https://example.com/fail',
        'https://example.com/back'
    ) extends ApplePay {
        public function mappedReferenceKeys(array $data): array
        {
            return parent::mappedReferenceKeys($data);
        }
    };

    $apiResponse = [
        'transactionStatus' => 'Success',
        'transactionID' => 'TXN-123',
        'reference' => 'AP-456',
        'redirectUrl' => 'https://payment.url/form',
    ];

    $mapped = $applePay->mappedReferenceKeys($apiResponse);

    expect($mapped)->toMatchArray([
        'success' => true,
        'transaction_status' => 'Success',
        'transaction_id' => 'TXN-123',
        'reference' => 'AP-456',
        'redirect_url' => 'https://payment.url/form',
    ]);
});

it('handles missing keys in API response gracefully', function () {
    $applePay = new class(
        100.00,
        'ORDER-1',
        'https://example.com/success',
        'https://example.com/fail',
        'https://example.com/back'
    ) extends ApplePay {
        public function mappedReferenceKeys(array $data): array
        {
            return parent::mappedReferenceKeys($data);
        }
    };

    $incompleteResponse = [
        'transactionStatus' => 'Success',
        'transactionID' => 'TXN-999',
    ];

    $mapped = $applePay->mappedReferenceKeys($incompleteResponse);

    expect($mapped)->toHaveKeys(['success', 'transaction_status', 'transaction_id', 'reference', 'redirect_url'])
        ->and($mapped['success'])->toBeTrue()
        ->and($mapped['transaction_id'])->toBe('TXN-999')
        ->and($mapped['reference'])->toBeNull()
        ->and($mapped['redirect_url'])->toBeNull();
});
