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

namespace ViaBill\Object\Handler;

/**
 * Class HandlerResponse
 * @package ViaBill\Object\Handler
 */
class HandlerResponse
{
    /**
     * Status Code Variable Declaration.
     *
     * @var
     */
    private $statusCode;

    /**
     * Errors Variable Declaration.
     *
     * @var string[]
     */
    private $errors;

    /**
     * Order Variable Declaration.
     *
     * @var \Order
     */
    private $order;

    /**
     * Success Message Variable Declaration.
     *
     * @var string
     */
    private $successMessage;

    /**
     * Warnings Variable Declaration.
     *
     * @var string[]
     */
    private $warnings;

    /**
     * HandlerResponse constructor.
     *
     * @param \Order $order
     * @param $statusCode
     * @param string[] $errors
     * @param string $successMessage
     * @param string[] $warnings
     */
    public function __construct(
        \Order $order,
        $statusCode,
        $errors = array(),
        $successMessage = '',
        $warnings = array()
    ) {
        $this->statusCode = $statusCode;
        $this->errors = $errors;
        $this->order = $order;
        $this->successMessage = $successMessage;
        $this->warnings = $warnings;
    }

    /**
     * Gets Order.
     *
     * @return \Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Gets Status Code.
     *
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Gets Errors.
     *
     * @return string[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Gets Success Message.
     *
     * @return string
     */
    public function getSuccessMessage()
    {
        return $this->successMessage;
    }

    /**
     * Gets Warnings.
     *
     * @return string[]
     */
    public function getWarnings()
    {
        return $this->warnings;
    }
}
