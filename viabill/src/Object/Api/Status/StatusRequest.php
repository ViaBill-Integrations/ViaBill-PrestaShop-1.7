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

namespace ViaBill\Object\Api\Status;

/**
 * Class StatusRequest
 */
class StatusRequest
{
    /**
     * Status Request Transaction Variable Declaration.
     *
     * @var string
     */
    private $transaction;

    /**
     * Status Request API Key Variable Declaration.
     *
     * @var string
     */
    private $apiKey;

    /**
     * Status Request Signature Variable Declaration.
     *
     * @var string
     */
    private $signature;

    /**
     * StatusRequest constructor.
     *
     * @param string $transaction
     * @param string $apiKey
     * @param string $signature
     */
    public function __construct($transaction, $apiKey, $signature)
    {
        $this->transaction = $transaction;
        $this->apiKey = $apiKey;
        $this->signature = $signature;
    }

    /**
     * Gets Status Request Transaction.
     *
     * @return string
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Gets Status Request API Key.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * Gets Status Request Signature.
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }
}
