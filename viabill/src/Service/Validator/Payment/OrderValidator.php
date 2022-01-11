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

namespace ViaBill\Service\Validator\Payment;

use Customer;
use Order;
use ViaBill\Adapter\Configuration;
use ViaBill\Config\Config;
use ViaBill\Object\Validator\ValidationResponse;

/**
 * Class OrderValidator
 */
class OrderValidator
{
    /**
     * Configuration Variable Declaration.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * OrderValidator constructor.
     *
     * @param \ViaBill $module
     * @param Configuration $configuration
     */
    public function __construct(\ViaBill $module, Configuration $configuration)
    {
        $this->configuration = $configuration;
        $this->module = $module;
    }

    /**
     * Validates Order.
     *
     * @param Order $order
     * @param Customer $customer
     *
     * @return ValidationResponse
     */
    public function validate(Order $order, Customer $customer)
    {
        $idCurrentState = $order->getCurrentState();

        $awaitingOrderState = $this->configuration->get(Config::PAYMENT_PENDING);
        $isOrderStateMatches = $awaitingOrderState == $idCurrentState;
        $isCustomersOrder = $order->id_customer == $customer->id;
        $isModulePayment = $order->module == $this->module->name;

        if (!$order->id ||
            !$isOrderStateMatches ||
            !$isCustomersOrder ||
            !$isModulePayment ||
            $order->hasBeenPaid()
        ) {
            return new ValidationResponse(false);
        }

        return new ValidationResponse(true);
    }

    /**
     * @param Order $order
     *
     * @return ValidationResponse
     */
    public function validateIsOrderWithModulePayment(Order $order)
    {
        $isModulePayment = $order->module == $this->module->name;

        return new ValidationResponse($isModulePayment);
    }
}
