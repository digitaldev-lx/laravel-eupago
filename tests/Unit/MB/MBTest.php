<?php

use DigitaldevLx\LaravelEupago\Events\MBReferenceCreated;
use DigitaldevLx\LaravelEupago\Events\MBReferenceCreationFailed;
use DigitaldevLx\LaravelEupago\MB\MB;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

it('creates MB reference successfully', function () {
    $mockResponse = json_encode([
        'sucesso' => true,
        'estado' => 0,
        'resposta' => 'OK',
        'entidade' => '11249',
        'referencia' => '123456789',
        'valor' => '100.00',
        'valor_minimo' => '100.00',
        'valor_maximo' => '100.00',
        'data_inicio' => '2024-01-01',
        'data_fim' => '2024-01-07',
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $mb = new class(100.00, 'ORDER-1', '2024-01-01', '2024-01-07', 100.00, 100.00) extends MB {
        public function createWithClient(Client $client)
        {
            $response = $client->post(self::URI, $this->getParams());
            $referenceData = json_decode($response->getBody()->getContents(), true);

            if (!$referenceData['sucesso']) {
                $this->addError($referenceData['estado'], $referenceData['resposta']);
                event(new MBReferenceCreationFailed($this->errors, []));
            } else {
                $mappedData = $this->mappedReferenceKeys($referenceData);
                event(new MBReferenceCreated($mappedData));
            }

            return $this->mappedReferenceKeys($referenceData);
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

    $result = $mb->createWithClient($client);

    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['reference'])->toBe('123456789')
        ->and($result['entity'])->toBe('11249');

    Event::assertDispatched(MBReferenceCreated::class);
});

it('handles MB reference creation failure', function () {
    $mockResponse = json_encode([
        'sucesso' => false,
        'estado' => 1,
        'resposta' => 'Invalid API key',
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $mb = new class(100.00, 'ORDER-1', '2024-01-01', '2024-01-07', 100.00, 100.00) extends MB {
        public function createWithClient(Client $client)
        {
            $response = $client->post(self::URI, $this->getParams());
            $referenceData = json_decode($response->getBody()->getContents(), true);

            if (!$referenceData['sucesso']) {
                $this->addError($referenceData['estado'], $referenceData['resposta']);
                event(new MBReferenceCreationFailed($this->errors, []));
            }

            return $this->mappedReferenceKeys($referenceData);
        }

        public function getParams(): array
        {
            return parent::getParams();
        }

        public function mappedReferenceKeys(array $data): array
        {
            return parent::mappedReferenceKeys($data);
        }

        public function getErrors(): array
        {
            return parent::getErrors();
        }

        public function hasErrors(): bool
        {
            return parent::hasErrors();
        }
    };

    $result = $mb->createWithClient($client);

    expect($mb->hasErrors())->toBeTrue()
        ->and($mb->getErrors())->toHaveKey(1)
        ->and($result['success'])->toBeFalse();

    Event::assertDispatched(MBReferenceCreationFailed::class);
});

it('builds correct parameters for API request', function () {
    config(['eupago.api_key' => 'test-key']);

    $mb = new class(100.00, 'ORDER-123', '2024-01-01', '2024-01-07', 50.00, 150.00, true) extends MB {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $mb->getParams();

    expect($params)->toHaveKey('form_params')
        ->and($params['form_params'])->toMatchArray([
            'chave' => 'test-key',
            'valor' => 100.00,
            'id' => 'ORDER-123',
            'data_inicio' => '2024-01-01',
            'data_fim' => '2024-01-07',
            'valor_minimo' => 50.00,
            'valor_maximo' => 150.00,
            'per_dup' => true,
        ]);
});

it('maps API response keys correctly', function () {
    $mb = new class(100.00, 'ORDER-1', '2024-01-01', '2024-01-07', 100.00, 100.00) extends MB {
        public function mappedReferenceKeys(array $data): array
        {
            return parent::mappedReferenceKeys($data);
        }
    };

    $apiResponse = [
        'sucesso' => true,
        'estado' => 0,
        'resposta' => 'OK',
        'entidade' => '11249',
        'referencia' => '123456789',
        'valor' => '100.00',
        'valor_minimo' => '100.00',
        'valor_maximo' => '100.00',
        'data_inicio' => '2024-01-01',
        'data_fim' => '2024-01-07',
    ];

    $mapped = $mb->mappedReferenceKeys($apiResponse);

    expect($mapped)->toMatchArray([
        'success' => true,
        'state' => 0,
        'response' => 'OK',
        'entity' => '11249',
        'reference' => '123456789',
        'value' => '100.00',
        'min_value' => '100.00',
        'max_value' => '100.00',
        'start_date' => '2024-01-01',
        'end_date' => '2024-01-07',
    ]);
});

it('handles missing keys in API response gracefully', function () {
    $mb = new class(100.00, 'ORDER-1', '2024-01-01', '2024-01-07', 100.00, 100.00) extends MB {
        public function mappedReferenceKeys(array $data): array
        {
            return parent::mappedReferenceKeys($data);
        }
    };

    $incompleteResponse = [
        'sucesso' => true,
        'referencia' => '123456789',
    ];

    $mapped = $mb->mappedReferenceKeys($incompleteResponse);

    expect($mapped)->toHaveKeys(['success', 'state', 'response', 'entity', 'reference'])
        ->and($mapped['success'])->toBeTrue()
        ->and($mapped['reference'])->toBe('123456789')
        ->and($mapped['state'])->toBeNull()
        ->and($mapped['entity'])->toBeNull();
});
