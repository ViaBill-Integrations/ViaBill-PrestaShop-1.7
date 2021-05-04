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

namespace ViaBill\Object\Api\Status;

use ViaBill\Object\Api\ApiResponseError;
use ViaBill\Object\Api\ObjectResponseInterface;

/**
 * Class StatusResponse
 * @package ViaBill\Object\Api\Status
 */
class StatusResponse implements ObjectResponseInterface
{
    /**
     * Status Response Status Variable Declaration.
     *
     * @var string
     */
    private $status;

    /**
     * Status Response Errors Variable Declaration.
     *
     * @var ApiResponseError[]
     */
    private $errors;

    /**
     * StatusResponse constructor.
     *
     * @param string $status
     * @param array $errors
     */
    public function __construct($status, $errors = array())
    {
        $this->status = $status;
        $this->errors = $errors;
    }

    /**
     * Gets Status Response Status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Checks If Status Response Has Errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Gets Status Response Errors.
     *
     * @return array|ApiResponseError[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Gets Status Response Error Names.
     *
     * @return string[]
     */
    public function getErrorNames()
    {
        if (!$this->hasErrors()) {
            return array();
        }

        $errors = array();
        foreach ($this->errors as $error) {
            $errors[] = $error->getError();
        }

        return $errors;
    }
}
