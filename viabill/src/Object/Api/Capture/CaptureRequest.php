<?php
/**
* NOTICE OF LICENSE
*
* @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
* @see       /LICENSE
*
*/

namespace ViaBill\Object\Api\Capture;

/**
 * Class CaptureRequest
 *
 * @package ViaBill\Object\Api\Capture
 */
class CaptureRequest
{
    /**
     * Capture Request ID Variable Declaration.
     *
     * @var string
     */
    private $id;

    /**
     * Capture Request API Key Variable Declaration.
     *
     * @var string
     */
    private $apikey;

    /**
     * Capture Request Signature Variable Declaration.
     *
     * @var string
     */
    private $signature;

    /**
     * Capture Request Amount Variable Declaration.
     *
     * @var string
     */
    private $amount;

    /**
     * Capture Request Currency Variable Declaration.
     *
     * @var string
     */
    private $currency;

    /**
     * CaptureRequest constructor.
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
        $this->signature = $signature;
        $this->amount = (string) $amount;
        $this->currency = $currency;
    }

    /**
     * Get Capture Request ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Capture Request API Key.
     *
     * @return string
     */
    public function getApikey()
    {
        return $this->apikey;
    }

    /**
     * Get Capture Request Signature.
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }

    /**
     * Get Capture Request Amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Get Capture Request Currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }
}
