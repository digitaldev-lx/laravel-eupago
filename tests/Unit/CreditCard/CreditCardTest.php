<?php

declare(strict_types=1);

use DigitaldevLx\LaravelEupago\CreditCard\CreditCard;
use DigitaldevLx\LaravelEupago\Events\CreditCardReferenceCreated;
use DigitaldevLx\LaravelEupago\Events\CreditCardReferenceCreationFailed;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

it('creates Credit Card reference successfully', function () {
    $mockResponse = json_encode([
        'transactionStatus' => 'Success',
        'transactionID' => 'TXN-123456789',
        'reference' => 'CC-987654321',
        'redirectUrl' => 'https://sandbox.eupago.pt/payment/cc-form?token=abc123',
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $creditCard = new class(100.00, 'ORDER-1', 'https://example.com/success', 'https://example.com/fail', 'https://example.com/back', 'test@example.com') extends CreditCard
    {
        public function createWithClient(Client $client)
        {
            $response = $client->post(self::URI, $this->getParams());
            $referenceData = json_decode($response->getBody()->getContents(), true);

            if (! isset($referenceData['transactionStatus']) || $referenceData['transactionStatus'] !== 'Success') {
                $errorMessage = $referenceData['message'] ?? 'Unknown error';
                $this->addError('error', $errorMessage);
                event(new CreditCardReferenceCreationFailed($this->errors, []));

                return ['success' => false, 'errors' => $this->errors];
            }

            $mappedData = $this->mappedReferenceKeys($referenceData);
            event(new CreditCardReferenceCreated($mappedData));

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

    $result = $creditCard->createWithClient($client);

    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['transaction_id'])->toBe('TXN-123456789')
        ->and($result['reference'])->toBe('CC-987654321')
        ->and($result['redirect_url'])->toBe('https://sandbox.eupago.pt/payment/cc-form?token=abc123');

    Event::assertDispatched(CreditCardReferenceCreated::class);
});

it('handles Credit Card reference creation failure', function () {
    $mockResponse = json_encode([
        'transactionStatus' => 'Failed',
        'message' => 'Invalid API key',
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $creditCard = new class(100.00, 'ORDER-1', 'https://example.com/success', 'https://example.com/fail', 'https://example.com/back', 'test@example.com') extends CreditCard
    {
        public function createWithClient(Client $client)
        {
            $response = $client->post(self::URI, $this->getParams());
            $referenceData = json_decode($response->getBody()->getContents(), true);

            if (! isset($referenceData['transactionStatus']) || $referenceData['transactionStatus'] !== 'Success') {
                $errorMessage = $referenceData['message'] ?? 'Unknown error';
                $this->addError('error', $errorMessage);
                event(new CreditCardReferenceCreationFailed($this->errors, []));

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

    $result = $creditCard->createWithClient($client);

    expect($creditCard->hasErrors())->toBeTrue()
        ->and($creditCard->getErrors())->toHaveKey('error')
        ->and($result['success'])->toBeFalse();

    Event::assertDispatched(CreditCardReferenceCreationFailed::class);
});

it('builds correct parameters for API request', function () {
    config(['eupago.api_key' => 'test-api-key']);

    $creditCard = new class(150.00, 'ORDER-456', 'https://example.com/success', 'https://example.com/fail', 'https://example.com/back', 'customer@example.com', 'EN', 'EUR', true, 60) extends CreditCard
    {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $creditCard->getParams();

    expect($params)->toHaveKeys(['headers', 'json'])
        ->and($params['headers'])->toMatchArray([
            'ApiKey' => 'test-api-key',
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
        ]);
});

it('builds parameters without optional minutesFormUp', function () {
    config(['eupago.api_key' => 'test-api-key']);

    $creditCard = new class(100.00, 'ORDER-789', 'https://example.com/success', 'https://example.com/fail', 'https://example.com/back', 'test@example.com') extends CreditCard
    {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $creditCard->getParams();

    expect($params['json']['payment'])->not->toHaveKey('minutesFormUp');
});

it('maps API response keys correctly', function () {
    $creditCard = new class(100.00, 'ORDER-1', 'https://example.com/success', 'https://example.com/fail', 'https://example.com/back', 'test@example.com') extends CreditCard
    {
        public function mappedReferenceKeys(array $data): array
        {
            return parent::mappedReferenceKeys($data);
        }
    };

    $apiResponse = [
        'transactionStatus' => 'Success',
        'transactionID' => 'TXN-123',
        'reference' => 'CC-456',
        'redirectUrl' => 'https://payment.url/form',
    ];

    $mapped = $creditCard->mappedReferenceKeys($apiResponse);

    expect($mapped)->toMatchArray([
        'success' => true,
        'transaction_status' => 'Success',
        'transaction_id' => 'TXN-123',
        'reference' => 'CC-456',
        'redirect_url' => 'https://payment.url/form',
    ]);
});

it('handles missing keys in API response gracefully', function () {
    $creditCard = new class(100.00, 'ORDER-1', 'https://example.com/success', 'https://example.com/fail', 'https://example.com/back', 'test@example.com') extends CreditCard
    {
        public function mappedReferenceKeys(array $data): array
        {
            return parent::mappedReferenceKeys($data);
        }
    };

    $incompleteResponse = [
        'transactionStatus' => 'Success',
        'transactionID' => 'TXN-999',
    ];

    $mapped = $creditCard->mappedReferenceKeys($incompleteResponse);

    expect($mapped)->toHaveKeys(['success', 'transaction_status', 'transaction_id', 'reference', 'redirect_url'])
        ->and($mapped['success'])->toBeTrue()
        ->and($mapped['transaction_id'])->toBe('TXN-999')
        ->and($mapped['reference'])->toBeNull()
        ->and($mapped['redirect_url'])->toBeNull();
});
