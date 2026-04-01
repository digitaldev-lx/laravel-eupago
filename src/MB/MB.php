<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago\MB;

use DigitaldevLx\LaravelEupago\EuPago;
use DigitaldevLx\LaravelEupago\Events\MBReferenceCreated;
use DigitaldevLx\LaravelEupago\Events\MBReferenceCreationFailed;
use GuzzleHttp\Client;

class MB extends EuPago
{
    public const string URI = '/clientes/rest_api/multibanco/create';

    public function __construct(
        protected readonly float $value,
        protected readonly string $id,
        protected readonly string $startDate,
        protected readonly string $endDate,
        protected readonly float $minValue,
        protected readonly float $maxValue,
        protected readonly bool $allowDuplication = false,
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

            event(new MBReferenceCreationFailed(
                $this->errors,
                [
                    'value' => $this->value,
                    'id' => $this->id,
                    'start_date' => $this->startDate,
                    'end_date' => $this->endDate,
                ]
            ));
        } else {
            $mappedData = $this->mappedReferenceKeys($referenceData);
            event(new MBReferenceCreated($mappedData));
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
            'entity' => $referenceData['entidade'] ?? null,
            'reference' => $referenceData['referencia'] ?? null,
            'value' => $referenceData['valor'] ?? null,
            'min_value' => $referenceData['valor_minimo'] ?? null,
            'max_value' => $referenceData['valor_maximo'] ?? null,
            'start_date' => $referenceData['data_inicio'] ?? null,
            'end_date' => $referenceData['data_fim'] ?? null,
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
                'data_inicio' => $this->startDate,
                'data_fim' => $this->endDate,
                'valor_minimo' => $this->minValue,
                'valor_maximo' => $this->maxValue,
                'per_dup' => $this->allowDuplication,
            ],
        ];
    }
}
