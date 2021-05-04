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

namespace ViaBill\Object\Api\Notification;

/**
 * Class NotificationResponse
 *
 * @package ViaBill\Object\Api\Notification
 */
class NotificationResponse
{
    /**
     * Notification Message Variable Declaration.
     *
     * @var string
     */
    private $message;

    /**
     * NotificationResponse constructor
     * .
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * Gets Notification Message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }
}
