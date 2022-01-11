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

namespace ViaBill\Service;

use Order;
use Tools;
use ViaBill\Adapter\Context;
use ViaBill\Builder\Message\OrderMessageBuilder;
use ViaBill\Config\Config;

/**
 * Class MessageService
 */
class MessageService
{
    /**
     * Filename Constant.
     */
    const FILENAME = 'MessageService';

    /**
     * Order Message Builder Variable Declaration.
     *
     * @var OrderMessageBuilder
     */
    private $orderMessageBuilder;

    /**
     * Context Variable Declaration.
     *
     * @var Context
     */
    private $context;

    /**
     * MessageService constructor.
     *
     * @param OrderMessageBuilder $orderMessageBuilder
     * @param Context $context
     */
    public function __construct(OrderMessageBuilder $orderMessageBuilder, Context $context)
    {
        $this->orderMessageBuilder = $orderMessageBuilder;
        $this->context = $context;
    }

    /**
     * Redirects To Admin Orders Controller With Messages.
     *
     * @param Order $order
     * @param array $confirmations
     * @param array $errors
     * @param array $warnings
     *
     * @throws \PrestaShopException
     */
    public function redirectWithMessages(Order $order, array $confirmations, array $errors, array $warnings)
    {
        $this->setMessages($confirmations, $errors, $warnings);

        if (Config::isVersionAbove177()) {
            $linkParams = ['orderId' => $order->id, 'vieworder' => 1];
        } else {
            $linkParams = ['id_order' => $order->id, 'vieworder' => 1];
        }

        $redirectLink = $this->context->getLink()->getAdminLink(
            'AdminOrders',
            true,
            [],
            $linkParams
        );
        Tools::redirectAdmin($redirectLink);
    }

    /**
     * Sets Controller Messages.
     *
     * @param array $confirmations
     * @param array $errors
     * @param array $warnings
     */
    public function setMessages(array $confirmations, array $errors, array $warnings)
    {
        $messageBuilder = $this->orderMessageBuilder;
        $messageBuilder->setContext($this->context->getContext());
        $messageBuilder->setErrorMessage($errors);
        $messageBuilder->setSuccessMessage($confirmations);
        $messageBuilder->setWarningMessage($warnings);
    }
}
