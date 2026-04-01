<?php

declare(strict_types=1);

namespace DigitaldevLx\LaravelEupago;

class EuPago
{
    public const string TEST_ENDPOINT = 'https://sandbox.eupago.pt';

    public const string PROD_ENDPOINT = 'https://clientes.eupago.pt';

    /** @var array<string|int, string> */
    protected array $errors = [];

    public function getBaseUri(): string
    {
        return config('eupago.env') === 'prod' ? self::PROD_ENDPOINT : self::TEST_ENDPOINT;
    }

    /** @return array<string|int, string> */
    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function addError(string|int $code, string $message): void
    {
        $this->errors[$code] = html_entity_decode($message);
    }

    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
}
