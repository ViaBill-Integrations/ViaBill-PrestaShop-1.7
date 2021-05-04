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

namespace ViaBill\Object;

/**
 * Class ViaBillUser
 *
 * @package ViaBill\Object
 */
class ViaBillUser
{
    /**
     * Key Variable Declaration.
     *
     * @var string
     */
    private $key;

    /**
     * Secret Variable Declaration.
     *
     * @var string
     */
    private $secret;

    /**
     * Script Variable Declaration.
     *
     * @var string
     */
    private $script;

    /**
     * ViaBillUser constructor.
     * @param string $key
     * @param string $secret
     * @param string $script
     */
    public function __construct($key, $secret, $script)
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->script = $script;
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
     * Gets User Secret.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Gets Script.
     *
     * @return string
     */
    public function getScript()
    {
        return $this->script;
    }

    /**
     * Gets Signature.
     *
     * @return string
     */
    public function getSignature()
    {
        return md5($this->key.'#'.$this->secret);
    }
}
