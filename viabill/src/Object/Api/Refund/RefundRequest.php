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

namespace ViaBill\Object\Api\Refund;

/**
 * Class RefundRequest
 */
class RefundRequest
{
    /**
     * Refund Request ID Variable Declaration.
     *
     * @var string
     */
    private $id;

    /**
     * Refund Request API Key Variable Declaration.
     *
     * @var string
     */
    private $apikey;

    /**
     * Refund Request Amount Variable Declaration.
     *
     * @var float
     */
    private $amount;

    /**
     * Refund Request Currency Variable Declaration.
     *
     * @var string
     */
    private $currency;

    /**
     * Refund Request Signature Variable Declaration.
     *
     * @var string
     */
    private $signature;

    /**
     * RefundRequest constructor.
     *
     * @param string $id - transaction field in checkout
     * @param string $apikey
     * @param string $signature
     * @param float $amount
     * @param string $currency
     */
    public function __construct($id, $apikey, $signature, $amount, $currency)
    {
        $this->id = $id;
        $this->apikey = $apikey;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->signature = $signature;
    }

    /**
     * Gets Refund Request ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets Refund Request API Key.
     *
     * @return string
     */
    public function getApikey()
    {
        return $this->apikey;
    }

    /**
     * Gets Refund Request Signature.
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Gets Refund Request Amount.
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Gets Refund Request Currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}
