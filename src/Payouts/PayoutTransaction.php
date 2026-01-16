<?php

namespace DigitaldevLx\LaravelEupago\Payouts;

use DigitaldevLx\LaravelEupago\EuPago;
use GuzzleHttp\Client;

class PayoutTransaction extends EuPago
{
    /**
     * The unique resource identifier.
     */
    const URI = '/api/management/v1.02/payouts/transactions';

    /**
     * Start date for transactions query.
     *
     * @var string
     */
    protected $startDate;

    /**
     * End date for transactions query.
     *
     * @var string
     */
    protected $endDate;

    /**
     * Bearer token for OAuth authentication.
     *
     * @var string
     */
    protected $bearerToken;

    /**
     * The errors stored during the operations.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * PayoutTransaction constructor.
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $bearerToken
     */
    public function __construct(
        string $startDate,
        string $endDate,
        string $bearerToken
    ) {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->bearerToken = $bearerToken;
    }

    /**
     * Returns the errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Adds an error to the bag.
     *
     * @param $code
     * @param $message
     */
    protected function addError($code, $message)
    {
        $this->errors[$code] = html_entity_decode($message);
    }

    /**
     * Determines whether errors are logged.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * Lists all payout transactions for the specified date range.
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function list(): array
    {
        $client = new Client(['base_uri' => $this->getBaseUri()]);

        try {
            $response = $client->get(self::URI, $this->getParams());
        } catch (\Exception $e) {
            throw $e;
        }

        $transactionsData = json_decode($response->getBody()->getContents(), true);

        if (isset($transactionsData['error'])) {
            $errorMessage = $transactionsData['message'] ?? $transactionsData['error'] ?? 'Unknown error';
            $errorCode = $transactionsData['code'] ?? 'error';
            $this->addError($errorCode, $errorMessage);

            return [
                'success' => false,
                'errors' => $this->errors,
            ];
        }

        return [
            'success' => true,
            'transactions' => $transactionsData,
        ];
    }

    /**
     * Returns the required params for making a request.
     *
     * @return array
     */
    protected function getParams(): array
    {
        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->bearerToken,
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
            ],
        ];
    }
}
