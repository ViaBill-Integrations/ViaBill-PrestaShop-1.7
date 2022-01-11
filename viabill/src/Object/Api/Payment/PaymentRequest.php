<?php
/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
*
* @see       /LICENSE
*/

namespace ViaBill\Object\Api\Payment;

use ViaBill\Object\Api\SerializedObjectInterface;

/**
 * Class PaymentRequest
 */
class PaymentRequest implements SerializedObjectInterface
{
    /**
     * Defines Payment Request Protocol.
     *
     * @var string
     */
    private $protocol = '3.0';

    /**
     * Payment Request API Key Variable Declaration.
     *
     * @var string
     */
    private $apiKey;

    /**
     * Payment Request Transaction Variable Declaration.
     *
     * @var string
     */
    private $transaction;

    /**
     * Payment Request Order Number Variable Declaration.
     *
     * @var string
     */
    private $order_number;

    /**
     * Payment Request Amount Variable Declaration.
     *
     * @var float
     */
    private $amount;

    /**
     * Payment Request Currency Variable Declaration.
     *
     * @var string
     */
    private $currency;

    /**
     * Payment Request Success Url Variable Declaration.
     *
     * @var string
     */
    private $success_url;

    /**
     * Payment Request Cancel URL Variable Declaration.
     *
     * @var string
     */
    private $cancel_url;

    /**
     * Payment Request Test Variable Declaration.
     *
     * @var bool
     */
    private $test;

    /**
     * Payment Request Callback URL Variable Declaration.
     *
     * @var string
     */
    private $callback_url;

    /**
     * Payment Request md5Check Variable Declaration.
     *
     * @var string
     */
    private $md5Check;

    /**
     * Payment Request Customer Info
     *
     * @var array
     */
    private $customParams;

    /**
     * PaymentRequest constructor.
     *
     * @param $apiKey
     * @param string $transaction
     * @param string $order_number
     * @param string $amount
     * @param string $currency
     * @param string $success_url
     * @param string $cancel_url
     * @param string $callback_url
     * @param bool $test
     * @param string $md5Check
     * @param array $ustomer_info
     */
    public function __construct(
        $apiKey,
        $transaction,
        $order_number,
        $amount,
        $currency,
        $success_url,
        $cancel_url,
        $callback_url,
        $test,
        $md5Check,
        $customParams
    ) {
        $this->apiKey = $apiKey;
        $this->transaction = $transaction;
        $this->order_number = $order_number;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->success_url = $success_url;
        $this->cancel_url = $cancel_url;
        $this->test = $test;
        $this->md5Check = $md5Check;
        $this->callback_url = $callback_url;
        $this->customParams = $this->cleanCustomParams($customParams);
    }

    /**
     * Clearn custom params to be compatible with viabill server
     *
     * @param array $customParams
     *
     * @return array
     */
    private function cleanCustomParams($customParams)
    {
        if (empty($customParams)) {
            return null;
        }

        $country = \Tools::strtoupper($customParams['country']);
        switch ($country) {
            case 'UNITED STATES':
                $customParams['country'] = 'US';
                break;
            case 'DENMARK':
                $customParams['country'] = 'DK';
                break;
            case 'SPAIN':
                $customParams['country'] = 'ES';
                break;
        }

        foreach ($customParams['country'] as &$value) {
            $value = str_replace("\n", " ", $value);
        }

        return $customParams;
    }

    /**
     * Gets Payment Request Protocol.
     *
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     * Gets Payment Request API Key Protocol.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Gets Payment Request Transaction.
     *
     * @return string
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Gets Payment Request Order Number.
     *
     * @return string
     */
    public function getOrderNumber()
    {
        return (string) $this->order_number;
    }

    /**
     * Gets Payment Request Amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return (string) $this->amount;
    }

    /**
     * Gets Payment Request Currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Gets Payment Request Success URL.
     *
     * @return string
     */
    public function getSuccessUrl()
    {
        return $this->success_url;
    }

    /**
     * Gets Payment Request Cancel URL.
     *
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancel_url;
    }

    /**
     * Gets Payment Request Calback URL.
     *
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->callback_url;
    }

    /**
     * Checks Is Test.
     *
     * @return bool
     */
    public function isTest()
    {
        return $this->test;
    }

    /**
     * Gets Payment Request Md5Check.
     *
     * @return string
     */
    public function getMd5check()
    {
        return $this->md5Check;
    }

    /**
     * Gets Customer Info.
     *
     * @return string
     */
    public function getCustomParams()
    {
        return json_encode($this->customParams);
    }

    /**
     * Gets Payment Request Serialized Data.
     *
     * @return array
     */
    public function getSerializedData()
    {
        return [
            'protocol' => $this->protocol,
            'apikey' => $this->apiKey,
            'transaction' => $this->transaction,
            'order_number' => (string) $this->order_number,
            'amount' => (string) $this->amount,
            'currency' => $this->currency,
            'success_url' => $this->success_url,
            'cancel_url' => $this->cancel_url,
            'callback_url' => $this->callback_url,
            'test' => (bool) $this->test,
            'md5check' => $this->md5Check,
            'customParams' => $this->customParams
        ];
    }
}
