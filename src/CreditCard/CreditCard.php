<?php

namespace DigitaldevLx\LaravelEupago\CreditCard;

use DigitaldevLx\LaravelEupago\EuPago;
use DigitaldevLx\LaravelEupago\Events\CreditCardReferenceCreated;
use DigitaldevLx\LaravelEupago\Events\CreditCardReferenceCreationFailed;
use GuzzleHttp\Client;

class CreditCard extends EuPago
{
    /**
     * The unique resource identifier.
     */
    const URI = '/api/v1.02/creditcard/create';

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
     * Success redirect URL.
     *
     * @var string
     */
    protected $successUrl;

    /**
     * Fail redirect URL.
     *
     * @var string
     */
    protected $failUrl;

    /**
     * Back URL.
     *
     * @var string
     */
    protected $backUrl;

    /**
     * Language code.
     *
     * @var string
     */
    protected $lang;

    /**
     * Customer email.
     *
     * @var string
     */
    protected $customerEmail;

    /**
     * Enable customer notifications.
     *
     * @var bool
     */
    protected $customerNotify;

    /**
     * Form availability duration in minutes.
     *
     * @var int|null
     */
    protected $minutesFormUp;

    /**
     * The errors stored during the operations.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * CreditCard constructor.
     *
     * @param float $value
     * @param string $identifier
     * @param string $successUrl
     * @param string $failUrl
     * @param string $backUrl
     * @param string $customerEmail
     * @param string $lang
     * @param string $currency
     * @param bool $customerNotify
     * @param int|null $minutesFormUp
     */
    public function __construct(
        float $value,
        string $identifier,
        string $successUrl,
        string $failUrl,
        string $backUrl,
        string $customerEmail,
        string $lang = 'PT',
        string $currency = 'EUR',
        bool $customerNotify = true,
        ?int $minutesFormUp = null
    ) {
        $this->value = $value;
        $this->identifier = $identifier;
        $this->successUrl = $successUrl;
        $this->failUrl = $failUrl;
        $this->backUrl = $backUrl;
        $this->customerEmail = $customerEmail;
        $this->lang = $lang;
        $this->currency = $currency;
        $this->customerNotify = $customerNotify;
        $this->minutesFormUp = $minutesFormUp;
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
     * Generates a new Credit Card reference.
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

        $referenceData = json_decode($response->getBody()->getContents(), true);

        if (!isset($referenceData['transactionStatus']) || $referenceData['transactionStatus'] !== 'Success') {
            $errorMessage = $referenceData['message'] ?? 'Unknown error';
            $this->addError('error', $errorMessage);

            event(new CreditCardReferenceCreationFailed(
                $this->errors,
                [
                    'value' => $this->value,
                    'identifier' => $this->identifier,
                    'customer_email' => $this->customerEmail,
                ]
            ));

            return [
                'success' => false,
                'errors' => $this->errors,
            ];
        }

        $mappedData = $this->mappedReferenceKeys($referenceData);
        event(new CreditCardReferenceCreated($mappedData));

        return $mappedData;
    }

    /**
     * Maps the reference data keys.
     *
     * @param array $referenceData
     * @return array
     */
    protected function mappedReferenceKeys(array $referenceData): array
    {
        return [
            'success' => true,
            'transaction_status' => $referenceData['transactionStatus'] ?? null,
            'transaction_id' => $referenceData['transactionID'] ?? null,
            'reference' => $referenceData['reference'] ?? null,
            'redirect_url' => $referenceData['redirectUrl'] ?? null,
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
                    'successUrl' => $this->successUrl,
                    'failUrl' => $this->failUrl,
                    'backUrl' => $this->backUrl,
                    'lang' => $this->lang,
                ],
                'customer' => [
                    'notify' => $this->customerNotify,
                    'email' => $this->customerEmail,
                ],
            ],
        ];

        if ($this->minutesFormUp !== null) {
            $params['json']['payment']['minutesFormUp'] = $this->minutesFormUp;
        }

        return $params;
    }
}
