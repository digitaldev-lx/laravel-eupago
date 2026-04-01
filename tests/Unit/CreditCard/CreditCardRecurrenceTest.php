<?php

declare(strict_types=1);

use DigitaldevLx\LaravelEupago\CreditCard\CreditCardRecurrence;
use DigitaldevLx\LaravelEupago\Events\CreditCardRecurrenceAuthorizationCreated;
use DigitaldevLx\LaravelEupago\Events\CreditCardRecurrenceAuthorizationFailed;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

it('creates Credit Card Recurrence Authorization successfully', function () {
    $mockResponse = json_encode([
        'transactionStatus' => 'Success',
        'statusSubs' => 'Pending',
        'subscriptionID' => 'SUB-123456789',
        'referenceSubs' => 'RSUB-987654321',
        'redirectUrl' => 'https://sandbox.eupago.pt/subscription/form?token=abc123',
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $recurrence = new class('CUSTOMER-123') extends CreditCardRecurrence
    {
        public function createWithClient(Client $client)
        {
            $response = $client->post(self::URI, $this->getParams());
            $responseData = json_decode($response->getBody()->getContents(), true);

            if (! isset($responseData['transactionStatus']) || $responseData['transactionStatus'] !== 'Success') {
                $errorMessage = $responseData['message'] ?? 'Unknown error';
                $this->addError('error', $errorMessage);
                event(new CreditCardRecurrenceAuthorizationFailed($this->errors, []));

                return ['success' => false, 'errors' => $this->errors];
            }

            $mappedData = $this->mappedResponseKeys($responseData);
            event(new CreditCardRecurrenceAuthorizationCreated($mappedData));

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
    };

    $result = $recurrence->createWithClient($client);

    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['subscription_id'])->toBe('SUB-123456789')
        ->and($result['reference_subs'])->toBe('RSUB-987654321')
        ->and($result['status_subs'])->toBe('Pending')
        ->and($result['redirect_url'])->toBe('https://sandbox.eupago.pt/subscription/form?token=abc123');

    Event::assertDispatched(CreditCardRecurrenceAuthorizationCreated::class);
});

it('handles Credit Card Recurrence Authorization creation failure', function () {
    $mockResponse = json_encode([
        'transactionStatus' => 'Failed',
        'message' => 'Invalid API key',
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $recurrence = new class('CUSTOMER-123') extends CreditCardRecurrence
    {
        public function createWithClient(Client $client)
        {
            $response = $client->post(self::URI, $this->getParams());
            $responseData = json_decode($response->getBody()->getContents(), true);

            if (! isset($responseData['transactionStatus']) || $responseData['transactionStatus'] !== 'Success') {
                $errorMessage = $responseData['message'] ?? 'Unknown error';
                $this->addError('error', $errorMessage);
                event(new CreditCardRecurrenceAuthorizationFailed($this->errors, []));

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

    $result = $recurrence->createWithClient($client);

    expect($recurrence->hasErrors())->toBeTrue()
        ->and($recurrence->getErrors())->toHaveKey('error')
        ->and($result['success'])->toBeFalse();

    Event::assertDispatched(CreditCardRecurrenceAuthorizationFailed::class);
});

it('builds correct parameters for API request', function () {
    config(['eupago.api_key' => 'demo-f298-22a3-1cea-101']);

    $recurrence = new class('CUSTOMER-456') extends CreditCardRecurrence
    {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $recurrence->getParams();

    expect($params)->toHaveKeys(['headers', 'json'])
        ->and($params['headers'])->toMatchArray([
            'ApiKey' => 'demo-f298-22a3-1cea-101',
            'Content-Type' => 'application/json',
        ])
        ->and($params['json']['payment'])->toMatchArray([
            'identifier' => 'CUSTOMER-456',
        ]);
});

it('maps API response keys correctly', function () {
    $recurrence = new class('CUSTOMER-123') extends CreditCardRecurrence
    {
        public function mappedResponseKeys(array $data): array
        {
            return parent::mappedResponseKeys($data);
        }
    };

    $apiResponse = [
        'transactionStatus' => 'Success',
        'statusSubs' => 'Pending',
        'subscriptionID' => 'SUB-999',
        'referenceSubs' => 'RSUB-888',
        'redirectUrl' => 'https://payment.url/subscription',
    ];

    $mapped = $recurrence->mappedResponseKeys($apiResponse);

    expect($mapped)->toMatchArray([
        'success' => true,
        'transaction_status' => 'Success',
        'status_subs' => 'Pending',
        'subscription_id' => 'SUB-999',
        'reference_subs' => 'RSUB-888',
        'redirect_url' => 'https://payment.url/subscription',
    ]);
});

it('handles missing keys in API response gracefully', function () {
    $recurrence = new class('CUSTOMER-123') extends CreditCardRecurrence
    {
        public function mappedResponseKeys(array $data): array
        {
            return parent::mappedResponseKeys($data);
        }
    };

    $incompleteResponse = [
        'transactionStatus' => 'Success',
        'subscriptionID' => 'SUB-777',
    ];

    $mapped = $recurrence->mappedResponseKeys($incompleteResponse);

    expect($mapped)->toHaveKeys(['success', 'transaction_status', 'status_subs', 'subscription_id', 'reference_subs', 'redirect_url'])
        ->and($mapped['success'])->toBeTrue()
        ->and($mapped['subscription_id'])->toBe('SUB-777')
        ->and($mapped['status_subs'])->toBeNull()
        ->and($mapped['redirect_url'])->toBeNull();
});
