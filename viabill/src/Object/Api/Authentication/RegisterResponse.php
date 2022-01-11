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

namespace ViaBill\Object\Api\Authentication;

use ViaBill\Object\Api\ApiResponseError;
use ViaBill\Object\Api\ObjectResponseInterface;

/**
 * Class RegisterResponse
 */
class RegisterResponse implements ObjectResponseInterface
{
    /**
     * Register Response Key Variable Declaration.
     *
     * @var string
     */
    private $key;

    /**
     * Register Response Secret Variable Declaration.
     *
     * @var string
     */
    private $secret;

    /**
     * Register Response Pricetag Script Variable Declaration.
     *
     * @var string
     */
    private $pricetagScript;

    /**
     * Register Response Errors Variable Declaration.
     *
     * @var ApiResponseError
     */
    private $errors;

    /**
     * RegisterResponse constructor.
     *
     * @param string $key
     * @param string $secret
     * @param string $pricetagScript
     * @param ApiResponseError[] $errors
     */
    public function __construct($key, $secret, $pricetagScript, array $errors = [])
    {
        $this->key = $key;
        $this->secret = $secret;
        $this->pricetagScript = $pricetagScript;
        $this->errors = $errors;
    }

    /**
     * Gets User Register Response Key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Gets User Register Response Secret.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Gets Pricetag Script From User Register Response.
     *
     * @return string
     */
    public function getPricetagScript()
    {
        return $this->pricetagScript;
    }

    /**
     * Checks If User Register Response Has Errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Gets User Register Response Errors.
     *
     * @return array|ApiResponseError|ApiResponseError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
