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

namespace ViaBill\Object\Api\Renew;

/**
 * Class RenewRequest
 */
class RenewRequest
{
    /**
     * Renew Request ID Variable Declaration.
     *
     * @var string
     */
    private $id;

    /**
     * Renew Request API Key Variable Declaration.
     *
     * @var string
     */
    private $apikey;

    /**
     * Renew Request Signature Variable Declaration.
     *
     * @var string
     */
    private $signature;

    /**
     * RenewRequest constructor.
     *
     * @param string $id - transaction field in checkout
     * @param string $apikey
     * @param string $signature
     */
    public function __construct($id, $apikey, $signature)
    {
        $this->id = $id;
        $this->apikey = $apikey;
        $this->signature = $signature;
    }

    /**
     * Gets Renew Request ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets Renew Request API Key.
     *
     * @return string
     */
    public function getApikey()
    {
        return $this->apikey;
    }

    /**
     * Gets Renew Request Signature.
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }
}
