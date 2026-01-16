<?php

namespace DigitaldevLx\LaravelEupago\CreditCard;

use DigitaldevLx\LaravelEupago\EuPago;
use DigitaldevLx\LaravelEupago\Events\CreditCardRecurrenceAuthorizationCreated;
use DigitaldevLx\LaravelEupago\Events\CreditCardRecurrenceAuthorizationFailed;
use GuzzleHttp\Client;

class CreditCardRecurrence extends EuPago
{
    /**
     * The unique resource identifier.
     */
    const URI = '/api/v1.02/creditcard/subscription';

    /**
     * External identifier. Ex: the order id or customer id.
     *
     * @var string
     */
    protected $identifier;

    /**
     * The errors stored during the operations.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * CreditCardRecurrence constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
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
     * Creates a new Credit Card Recurrence Authorization.
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(): array
    {
        $client = new Client(['base_uri' => $this->getBaseUri()]);

        try {
            $response = $client->post(self::URI, $this->getParams());
        } catch (\Exception $e) {
            throw $e;
        }

        $responseData = json_decode($response->getBody()->getContents(), true);

        if (!isset($responseData['transactionStatus']) || $responseData['transactionStatus'] !== 'Success') {
            $errorMessage = $responseData['message'] ?? 'Unknown error';
            $this->addError('error', $errorMessage);

            event(new CreditCardRecurrenceAuthorizationFailed(
                $this->errors,
                [
                    'identifier' => $this->identifier,
                ]
            ));

            return [
                'success' => false,
                'errors' => $this->errors,
            ];
        }

        $mappedData = $this->mappedResponseKeys($responseData);
        event(new CreditCardRecurrenceAuthorizationCreated($mappedData));

        return $mappedData;
    }

    /**
     * Maps the response data keys.
     *
     * @param array $responseData
     * @return array
     */
    protected function mappedResponseKeys(array $responseData): array
    {
        return [
            'success' => true,
            'transaction_status' => $responseData['transactionStatus'] ?? null,
            'status_subs' => $responseData['statusSubs'] ?? null,
            'subscription_id' => $responseData['subscriptionID'] ?? null,
            'reference_subs' => $responseData['referenceSubs'] ?? null,
            'redirect_url' => $responseData['redirectUrl'] ?? null,
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
                'ApiKey' => config('eupago.api_key'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'payment' => [
                    'identifier' => $this->identifier,
                ],
            ],
        ];
    }
}
