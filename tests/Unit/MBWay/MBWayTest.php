<?php

use DigitaldevLx\LaravelEupago\Events\MBWayReferenceCreated;
use DigitaldevLx\LaravelEupago\Events\MBWayReferenceCreationFailed;
use DigitaldevLx\LaravelEupago\MBWay\MBWay;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

it('creates MBWay reference successfully', function () {
    $mockResponse = json_encode([
        'sucesso' => true,
        'estado' => 0,
        'resposta' => 'OK',
        'referencia' => '987654321',
        'valor' => '50.00',
        'alias' => '+351912345678',
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $mbway = new class(50.00, 1, '+351912345678', 'Test payment') extends MBWay {
        public function createWithClient(Client $client)
        {
            $response = $client->post(self::URI, $this->getParams());
            $referenceData = json_decode($response->getBody()->getContents(), true);

            if (!$referenceData['sucesso']) {
                $this->addError($referenceData['estado'], $referenceData['resposta']);
                event(new MBWayReferenceCreationFailed($this->errors, []));
            } else {
                $mappedData = $this->mappedReferenceKeys($referenceData);
                event(new MBWayReferenceCreated($mappedData));
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

    $result = $mbway->createWithClient($client);

    expect($result)->toBeArray()
        ->and($result['success'])->toBeTrue()
        ->and($result['reference'])->toBe('987654321')
        ->and($result['alias'])->toBe('+351912345678');

    Event::assertDispatched(MBWayReferenceCreated::class);
});

it('handles MBWay reference creation failure', function () {
    $mockResponse = json_encode([
        'sucesso' => false,
        'estado' => 2,
        'resposta' => 'Invalid phone number',
    ]);

    $mock = new MockHandler([
        new Response(200, [], $mockResponse),
    ]);

    $handlerStack = HandlerStack::create($mock);
    $client = new Client(['handler' => $handlerStack]);

    $mbway = new class(50.00, 1, 'invalid', null) extends MBWay {
        public function createWithClient(Client $client)
        {
            $response = $client->post(self::URI, $this->getParams());
            $referenceData = json_decode($response->getBody()->getContents(), true);

            if (!$referenceData['sucesso']) {
                $this->addError($referenceData['estado'], $referenceData['resposta']);
                event(new MBWayReferenceCreationFailed($this->errors, []));
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

    $result = $mbway->createWithClient($client);

    expect($mbway->hasErrors())->toBeTrue()
        ->and($mbway->getErrors())->toHaveKey(2)
        ->and($result['success'])->toBeFalse();

    Event::assertDispatched(MBWayReferenceCreationFailed::class);
});

it('builds correct parameters for MBWay API request', function () {
    config(['eupago.api_key' => 'test-key']);

    $mbway = new class(75.50, 123, '+351999888777', 'Test description') extends MBWay {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $mbway->getParams();

    expect($params)->toHaveKey('form_params')
        ->and($params['form_params'])->toMatchArray([
            'chave' => 'test-key',
            'valor' => 75.50,
            'id' => 123,
            'alias' => '+351999888777',
            'descricao' => 'Test description',
        ]);
});

it('handles null description parameter', function () {
    config(['eupago.api_key' => 'test-key']);

    $mbway = new class(50.00, 1, '+351912345678', null) extends MBWay {
        public function getParams(): array
        {
            return parent::getParams();
        }
    };

    $params = $mbway->getParams();

    expect($params['form_params']['descricao'])->toBeNull();
});

it('maps MBWay API response keys correctly', function () {
    $mbway = new class(50.00, 1, '+351912345678', null) extends MBWay {
        public function mappedReferenceKeys(array $data): array
        {
            return parent::mappedReferenceKeys($data);
        }
    };

    $apiResponse = [
        'sucesso' => true,
        'estado' => 0,
        'resposta' => 'OK',
        'referencia' => '987654321',
        'valor' => '50.00',
        'alias' => '+351912345678',
    ];

    $mapped = $mbway->mappedReferenceKeys($apiResponse);

    expect($mapped)->toMatchArray([
        'success' => true,
        'state' => 0,
        'response' => 'OK',
        'reference' => '987654321',
        'value' => '50.00',
        'alias' => '+351912345678',
    ]);
});
