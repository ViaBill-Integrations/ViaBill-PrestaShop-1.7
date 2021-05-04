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
 * Class ApiResponseError
 *
 * @package ViaBill\Object\Api
 */
class ApiResponseError
{
    /**
     * API Response Error Field Variable Declaration.
     *
     * @var string
     */
    private $field;

    /**
     * API Response Error Variable Declaration.
     *
     * @var string
     */
    private $error;

    /**
     * ApiResponseError constructor.
     *
     * @param string $field
     * @param string $error
     */
    public function __construct($field, $error)
    {
        $this->field = $field;
        $this->error = $error;
    }

    /**
     * Gets API Response Error Field.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Gets API Response Error.
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
