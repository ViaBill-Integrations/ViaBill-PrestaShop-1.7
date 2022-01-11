<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
*
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Viabill */

namespace ViaBill\Object\Api\Link;

/**
 * Class LinkRequest
 */
class LinkRequest
{
    /**
     * Key Variable Declaration.
     *
     * @var string
     */
    private $key;

    /**
     * Signature Variable Declaration.
     *
     * @var string
     */
    private $signature;

    /**
     * LinkRequest constructor.
     *
     * @param $key
     * @param $signature
     */
    public function __construct($key, $signature)
    {
        $this->key = $key;
        $this->signature = $signature;
    }

    /**
     * Gets User Key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Gets User Signature.
     *
     * @return string
     */
    public function getSignature()
    {
        return $this->signature;
    }
}
