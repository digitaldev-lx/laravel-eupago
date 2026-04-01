<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\MBWay;

use DigitaldevLx\LaravelEupago\EuPago;
use DigitaldevLx\LaravelEupago\Events\MBWayReferenceCreated;
use DigitaldevLx\LaravelEupago\Events\MBWayReferenceCreationFailed;
use GuzzleHttp\Client;

class MBWay extends EuPago
{
    public const string URI = '/clientes/rest_api/mbway/create';

    public function __construct(
        protected readonly float $value,
        protected readonly int $id,
        protected readonly string $alias,
        protected readonly ?string $description = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function create(): array
    {
        $client = new Client(['base_uri' => $this->getBaseUri()]);

        $response = $client->post(self::URI, $this->getParams());

        /** @var array<string, mixed> $referenceData */
        $referenceData = json_decode($response->getBody()->getContents(), true);

        if (! $referenceData['sucesso']) {
            $this->addError((string) $referenceData['estado'], (string) $referenceData['resposta']);

            event(new MBWayReferenceCreationFailed(
                $this->errors,
                [
                    'value' => $this->value,
                    'id' => $this->id,
                    'alias' => $this->alias,
                ]
            ));
        } else {
            $mappedData = $this->mappedReferenceKeys($referenceData);
            event(new MBWayReferenceCreated($mappedData));
        }

        return $this->mappedReferenceKeys($referenceData);
    }

    /**
     * @param  array<string, mixed>  $referenceData
     * @return array<string, mixed>
     */
    protected function mappedReferenceKeys(array $referenceData): array
    {
        return [
            'success' => $referenceData['sucesso'] ?? null,
            'state' => $referenceData['estado'] ?? null,
            'response' => $referenceData['resposta'] ?? null,
            'reference' => $referenceData['referencia'] ?? null,
            'value' => $referenceData['valor'] ?? null,
            'alias' => $referenceData['alias'] ?? null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getParams(): array
    {
        return [
            'form_params' => [
                'chave' => config('eupago.api_key'),
                'valor' => $this->value,
                'id' => $this->id,
                'alias' => $this->alias,
                'descricao' => $this->description,
            ],
        ];
    }
}
