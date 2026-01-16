<?php

namespace DigitaldevLx\LaravelEupago\CreditCard;

use DigitaldevLx\LaravelEupago\EuPago;
use DigitaldevLx\LaravelEupago\Events\CreditCardRecurringPaymentCreated;
use DigitaldevLx\LaravelEupago\Events\CreditCardRecurringPaymentFailed;
use GuzzleHttp\Client;

class CreditCardRecurringPayment extends EuPago
{
    /**
     * The unique resource identifier.
     */
    const URI = '/api/v1.02/creditcard/payment/';

    /**
     * The subscription ID (recurrentID).
     *
     * @var string
     */
    protected $subscriptionId;

    /**
     * The payment value.
     *
     * @var float
     */
    protected $value;

    /**
     * External identifier. Ex: the order id.
     *
     * @var string
     */
    protected $identifier;

    /**
     * Currency code.
     *
     * @var string
     */
    protected $currency;

    /**
     * Customer email.
     *
     * @var string|null
     */
    protected $customerEmail;

    /**
     * Enable customer notifications.
     *
     * @var bool
     */
    protected $customerNotify;

    /**
     * The errors stored during the operations.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * CreditCardRecurringPayment constructor.
     *
     * @param string $subscriptionId
     * @param float $value
     * @param string $identifier
     * @param string $currency
     * @param string|null $customerEmail
     * @param bool $customerNotify
     */
    public function __construct(
        string $subscriptionId,
        float $value,
        string $identifier,
        string $currency = 'EUR',
        ?string $customerEmail = null,
        bool $customerNotify = false
    ) {
        $this->subscriptionId = $subscriptionId;
        $this->value = $value;
        $this->identifier = $identifier;
        $this->currency = $currency;
        $this->customerEmail = $customerEmail;
        $this->customerNotify = $customerNotify;
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
     * Creates a new recurring payment.
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function create(): array
    {
        $client = new Client(['base_uri' => $this->getBaseUri()]);

        try {
            $response = $client->post(self::URI . $this->subscriptionId, $this->getParams());
        } catch (\Exception $e) {
            throw $e;
        }

        $responseData = json_decode($response->getBody()->getContents(), true);

        if (!isset($responseData['transactionStatus']) || $responseData['transactionStatus'] !== 'Success') {
            $errorMessage = $responseData['text'] ?? $responseData['message'] ?? 'Unknown error';
            $errorCode = $responseData['code'] ?? 'error';
            $this->addError($errorCode, $errorMessage);

            event(new CreditCardRecurringPaymentFailed(
                $this->errors,
                [
                    'subscription_id' => $this->subscriptionId,
                    'value' => $this->value,
                    'identifier' => $this->identifier,
                ]
            ));

            return [
                'success' => false,
                'errors' => $this->errors,
            ];
        }

        $mappedData = $this->mappedResponseKeys($responseData);
        event(new CreditCardRecurringPaymentCreated($mappedData));

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
            'status' => $responseData['status'] ?? null,
            'transaction_id' => $responseData['transactionID'] ?? null,
            'reference' => $responseData['reference'] ?? null,
            'message' => $responseData['message'] ?? null,
        ];
    }

    /**
     * Returns the required params for making a request.
     *
     * @return array
     */
    protected function getParams(): array
    {
        $params = [
            'headers' => [
                'ApiKey' => config('eupago.api_key'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'payment' => [
                    'identifier' => $this->identifier,
                    'amount' => [
                        'value' => $this->value,
                        'currency' => $this->currency,
                    ],
                ],
            ],
        ];

        if ($this->customerEmail && $this->customerNotify) {
            $params['json']['customer'] = [
                'notify' => $this->customerNotify,
                'email' => $this->customerEmail,
            ];
        }

        return $params;
    }
}
