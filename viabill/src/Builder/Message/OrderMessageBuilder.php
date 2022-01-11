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

namespace ViaBill\Builder\Message;

use Context;
use ViaBill\Adapter\Tools;

/**
 * Class OrderMessageBuilder
 */
class OrderMessageBuilder implements MessageBuilderInterface
{
    /**
     * Context Variable Declaration.
     *
     * @var Context
     */
    private $context;

    /**
     * Tools Variable Declaration.
     *
     * @var Tools
     */
    private $tools;

    /**
     * OrderMessageBuilder constructor.
     *
     * @param Tools $tools
     */
    public function __construct(Tools $tools)
    {
        $this->tools = $tools;
    }

    /**
     * Sets Context From Given Param.
     *
     * @param Context $context
     *
     * @return mixed|void
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Sets Order Success Message.
     *
     * @param string|string[] $message
     */
    public function setSuccessMessage($message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        } else {
            $message = json_encode([$message]);
        }

        $this->context->cookie->viaBillSuccessMessage = $message;
    }

    /**
     * Sets Order Warning Message.
     *
     * @param string|string[] $message
     */
    public function setWarningMessage($message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        } else {
            $message = json_encode([$message]);
        }

        $this->context->cookie->viaBillWarningMessage = $message;
    }

    /**
     * Sets Order Error Message.
     *
     * @param string|string[] $message
     */
    public function setErrorMessage($message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        } else {
            $message = json_encode([$message]);
        }

        $this->context->cookie->viaBillErrorMessage = $message;
    }

    /**
     * Displays Order Confirmation Message.
     *
     * @return mixed|void
     */
    public function displayConfirmationMessage()
    {
        if (isset($this->context->cookie->viaBillSuccessMessage)) {
            $this->context->controller->confirmations =
                json_decode($this->context->cookie->viaBillSuccessMessage);
        }

        unset($this->context->cookie->viaBillSuccessMessage);
    }

    /**
     * Displays Order Error Message.
     *
     * @return mixed|void
     */
    public function displayErrorMessage()
    {
        if (isset($this->context->cookie->viaBillErrorMessage)) {
            $this->context->controller->errors =
                json_decode($this->context->cookie->viaBillErrorMessage);
        }

        unset($this->context->cookie->viaBillErrorMessage);
    }

    /**
     * Displays Order Warning Message.
     */
    public function displayWarningMessage()
    {
        if (isset($this->context->cookie->viaBillWarningMessage)) {
            $this->context->controller->warnings =
                json_decode($this->context->cookie->viaBillWarningMessage);
        }

        unset($this->context->cookie->viaBillWarningMessage);
    }
}
