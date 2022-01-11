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
 * Class LoginResponse
 */
class LoginResponse implements ObjectResponseInterface
{
    /**
     * Login Response Key Variable Declaration.
     *
     * @var string
     */
    private $key;

    /**
     * RLogin esponse Secret Variable Declaration.
     *
     * @var string
     */
    private $secret;

    /**
     * Login Response Pricetag Script Variable Declaration.
     *
     * @var string
     */
    private $pricetagScript;

    /**
     * Login Response Errors Variable Declaration.
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
     * Gets User Login Response Key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Gets User Login Response Secret.
     *
     * @return string
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * Gets Pricetag Script From Login Response.
     *
     * @return string
     */
    public function getPricetagScript()
    {
        return $this->pricetagScript;
    }

    /**
     * Checks If Login Response Has Errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Gets Errors From Login Response.
     *
     * @return array|ApiResponseError|ApiResponseError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
