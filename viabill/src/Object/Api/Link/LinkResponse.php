<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Viabill */

namespace ViaBill\Object\Api\Link;

use ViaBill\Object\Api\ApiResponseError;
use ViaBill\Object\Api\ObjectResponseInterface;

/**
 * Class LoginResponse
 *
 * @package ViaBill\Object\Api\Authentication
 */
class LinkResponse implements ObjectResponseInterface
{
    /**
     * Link Response Link Variable Declaration.
     *
     * @var string
     */
    private $link;


    /**
     * Login Response Errors Variable Declaration.
     *
     * @var ApiResponseError
     */
    private $errors;

    /**
     * LinkResponse constructor.
     *
     * @param string $link
     * @param array $errors
     */
    public function __construct($link, array $errors = array())
    {
        $this->link = $link;
        $this->errors = $errors;
    }

    /**
     * Gets Link From Response.
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Checks If Link Response Has Errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Gets Errors From Link Response.
     *
     * @return array|ApiResponseError|ApiResponseError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
