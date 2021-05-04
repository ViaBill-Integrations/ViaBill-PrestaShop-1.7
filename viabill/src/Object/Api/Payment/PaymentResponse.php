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

namespace ViaBill\Object\Api\Payment;

use ViaBill\Object\Api\ApiResponseError;

/**
 * Class PaymentResponse
 *
 * @package ViaBill\Object\Api\Payment
 */
class PaymentResponse
{
    /**
     * Payment Response Effective URL Variable Declaration.
     *
     * @var string
     */
    private $effectiveUrl;

    /**
     * Payment Response Errors Variable Declaration.
     *
     * @var array|ApiResponseError[]
     */
    private $errors;

    /**
     * PaymentResponse constructor.
     *
     * @param string $effectiveUrl
     * @param ApiResponseError[] $errors
     */
    public function __construct($effectiveUrl, $errors = array())
    {
        $this->effectiveUrl = $effectiveUrl;
        $this->errors = $errors;
    }

    /**
     * Gets Payment Response Effective URL.
     *
     * @return string
     */
    public function getEffectiveUrl()
    {
        return $this->effectiveUrl;
    }

    /**
     * Gets Payment Response Errors.
     *
     * @return array|ApiResponseError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
