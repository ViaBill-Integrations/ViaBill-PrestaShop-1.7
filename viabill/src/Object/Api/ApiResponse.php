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

namespace ViaBill\Object\Api;

/**
 * Class ApiResponse
 *
 * @package ViaBill\Object\Api
 */
class ApiResponse
{
    /**
     * API Response Status Code Variable Declaration.
     *
     * @var int
     */
    private $statusCode;

    /**
     * API Response Body Variable Declaration.
     *
     * @var string
     */
    private $body;

    /**
     * API Response Errors Variable Declaration.
     *
     * @var ApiResponseError[]
     */
    private $errors;

    /**
     * API Response Effective URL Variable Declaration.
     *
     * @var string
     */
    private $effectiveUrl;

    /**
     * ApiResponse constructor.
     *
     * @param int $statusCode
     * @param string $body
     * @param ApiResponseError[] $errors
     * @param string $effectiveUrl
     */
    public function __construct($statusCode, $body, $errors = array(), $effectiveUrl = '')
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->errors = $errors;
        $this->effectiveUrl = $effectiveUrl;
    }

    /**
     * Gets API Response Status Code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Gets API Response Effective URl.
     *
     * @return string
     */
    public function getEffectiveUrl()
    {
        return $this->effectiveUrl;
    }

    /**
     * Gets API Response Body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Gets API Response Errors.
     *
     * @return ApiResponseError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
