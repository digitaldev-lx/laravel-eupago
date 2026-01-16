<?php

use DigitaldevLx\LaravelEupago\CreditCard\CreditCardRecurringPayment;
use DigitaldevLx\LaravelEupago\Events\CreditCardRecurringPaymentCreated;
use DigitaldevLx\LaravelEupago\Events\CreditCardRecurringPaymentFailed;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

it('creates Credit Card Recurring Payment successfully', function () {
    $mockResponse = json_encode([
        'transactionStatus' => 'Success',
        'status' => 'Paid',
        'transactionID' => 'TXN-REC-123456789',
        'reference' => 'REC-987654321',
        'message' => 'Payment has been executed successfully.',
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $payment = new class(
        'SUB-123',
        100.00,
        'ORDER-456',
        'EUR',
        'customer@example.com',
        true
    ) extends CreditCardRecurringPayment {
        public function createWithClient(Client $client)
        {
            $response = $client->post(self::URI . $this->subscriptionId, $this->getParams());
            $responseData = json_decode($response->getBody()->getContents(), true);

            if (!isset($responseData['transactionStatus']) || $responseData['transactionStatus'] !== 'Success') {
                $errorMessage = $responseData['text'] ?? $responseData['message'] ?? 'Unknown error';
                $errorCode = $responseData['code'] ?? 'error';
                $this->addError($errorCode, $errorMessage);
                event(new CreditCardRecurringPaymentFailed($this->errors, []));

                return ['success' => false, 'errors' => $this->errors];
            }

            $mappedData = $this->mappedResponseKeys($responseData);
            event(new CreditCardRecurringPaymentCreated($mappedData));

            return $mappedData;
        }

        public function getParams(): array
        {
            return parent::getParams();
        }

        public function mappedResponseKeys(array $data): array
        {
            return parent::mappedResponseKeys($data);
        }

        public function __get($property)
        {
            return $this->$property;
        }
    };

    $result = $payment->createWithClient($client);

    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['status'])->toBe('Paid')
        ->and($result['transaction_id'])->toBe('TXN-REC-123456789')
        ->and($result['reference'])->toBe('REC-987654321')
        ->and($result['message'])->toBe('Payment has been executed successfully.');

    Event::assertDispatched(CreditCardRecurringPaymentCreated::class);
});

it('handles Credit Card Recurring Payment creation failure', function () {
    $mockResponse = json_encode([
        'transactionStatus' => 'Failed',
        'code' => 'INSUFFICIENT_FUNDS',
        'text' => 'Insufficient funds',
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $payment = new class(
        'SUB-123',
        100.00,
        'ORDER-456'
    ) extends CreditCardRecurringPayment {
        public function createWithClient(Client $client)
        {
            $response = $client->post(self::URI . $this->subscriptionId, $this->getParams());
            $responseData = json_decode($response->getBody()->getContents(), true);

            if (!isset($responseData['transactionStatus']) || $responseData['transactionStatus'] !== 'Success') {
                $errorMessage = $responseData['text'] ?? $responseData['message'] ?? 'Unknown error';
                $errorCode = $responseData['code'] ?? 'error';
                $this->addError($errorCode, $errorMessage);
                event(new CreditCardRecurringPaymentFailed($this->errors, []));

                return ['success' => false, 'errors' => $this->errors];
            }

            return $this->mappedResponseKeys($responseData);
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

    $result = $payment->createWithClient($client);

    expect($payment->hasErrors())->toBeTrue()
        ->and($payment->getErrors())->toHaveKey('INSUFFICIENT_FUNDS')
        ->and($result['success'])->toBeFalse();

    Event::assertDispatched(CreditCardRecurringPaymentFailed::class);
});

it('builds correct parameters for API request with customer notification', function () {
    config(['eupago.api_key' => 'demo-f298-22a3-1cea-101']);

    $payment = new class(
        'SUB-789',
        250.50,
        'ORDER-999',
        'EUR',
        'customer@test.com',
        true
    ) extends CreditCardRecurringPayment {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $payment->getParams();

    expect($params)->toHaveKeys(['headers', 'json'])
        ->and($params['headers'])->toMatchArray([
            'ApiKey' => 'demo-f298-22a3-1cea-101',
            'Content-Type' => 'application/json',
        ])
        ->and($params['json']['payment'])->toMatchArray([
            'identifier' => 'ORDER-999',
            'amount' => [
                'value' => 250.50,
                'currency' => 'EUR',
            ],
        ])
        ->and($params['json']['customer'])->toMatchArray([
            'notify' => true,
            'email' => 'customer@test.com',
        ]);
});

it('builds parameters without customer notification', function () {
    config(['eupago.api_key' => 'demo-f298-22a3-1cea-101']);

    $payment = new class(
        'SUB-789',
        100.00,
        'ORDER-999'
    ) extends CreditCardRecurringPayment {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $payment->getParams();

    expect($params['json'])->not->toHaveKey('customer');
});

it('maps API response keys correctly', function () {
    $payment = new class(
        'SUB-123',
        100.00,
        'ORDER-456'
    ) extends CreditCardRecurringPayment {
        public function mappedResponseKeys(array $data): array
        {
            return parent::mappedResponseKeys($data);
        }
    };

    $apiResponse = [
        'transactionStatus' => 'Success',
        'status' => 'Paid',
        'transactionID' => 'TXN-555',
        'reference' => 'REF-666',
        'message' => 'Payment successful',
    ];

    $mapped = $payment->mappedResponseKeys($apiResponse);

    expect($mapped)->toMatchArray([
        'success' => true,
        'transaction_status' => 'Success',
        'status' => 'Paid',
        'transaction_id' => 'TXN-555',
        'reference' => 'REF-666',
        'message' => 'Payment successful',
    ]);
});

it('handles missing keys in API response gracefully', function () {
    $payment = new class(
        'SUB-123',
        100.00,
        'ORDER-456'
    ) extends CreditCardRecurringPayment {
        public function mappedResponseKeys(array $data): array
        {
            return parent::mappedResponseKeys($data);
        }
    };

    $incompleteResponse = [
        'transactionStatus' => 'Success',
        'transactionID' => 'TXN-999',
    ];

    $mapped = $payment->mappedResponseKeys($incompleteResponse);

    expect($mapped)->toHaveKeys(['success', 'transaction_status', 'status', 'transaction_id', 'reference', 'message'])
        ->and($mapped['success'])->toBeTrue()
        ->and($mapped['transaction_id'])->toBe('TXN-999')
        ->and($mapped['status'])->toBeNull()
        ->and($mapped['reference'])->toBeNull();
});
