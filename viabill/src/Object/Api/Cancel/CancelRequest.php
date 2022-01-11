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

namespace ViaBill\Object\Api\Cancel;

/**
 * Class CancelRequest
 */
class CancelRequest
{
    /**
     * Cancel Request ID Variable Declaration.
     *
     * @var string
     */
    private $id;

    /**
     * Cancel Request API Key Variable Declaration.
     *
     * @var string
     */
    private $apikey;

    /**
     * Cancel Request Signature Variable Declaration.
     *
     * @var string
     */
    private $signature;

    /**
     * CancelRequest constructor.
     *
     * @param string $id - transaction field in checkout
     * @param string $apikey
     * @param string $signature
     */
    public function __construct($id, $apikey, $signature)
    {
        $this->id = $id;
        $this->signature = $signature;
        $this->apikey = $apikey;
    }

    /**
     * Gets Cancel Request ID.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets Cancel Request API Key.
     *
     * @return string
     */
    public function getApikey()
    {
        return $this->apikey;
    }

    /**
     * Gets Cancel Request Signature.
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }
}
