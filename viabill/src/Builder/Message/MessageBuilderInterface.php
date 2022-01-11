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

/**
 * Interface MessageBuilderInterface
 */
interface MessageBuilderInterface
{
    /**
     * Sets Context.
     *
     * @param Context $context
     *
     * @return mixed
     */
    public function setContext(Context $context);

    /**
     * Sets Success Message.
     *
     * @param string[]|string $message
     */
    public function setSuccessMessage($message);

    /**
     * Sets Error Message.
     *
     * @param string[]|string $message
     */
    public function setErrorMessage($message);

    /**
     * Sets Warning Message.
     *
     * @param string[]|string $message
     */
    public function setWarningMessage($message);

    /**
     * Displays Confirmation Message.
     *
     * @return mixed
     */
    public function displayConfirmationMessage();

    /**
     * Displays Error Message.
     *
     * @return mixed
     */
    public function displayErrorMessage();
}
