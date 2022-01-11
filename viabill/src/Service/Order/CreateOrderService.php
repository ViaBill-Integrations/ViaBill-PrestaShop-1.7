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

namespace ViaBill\Service\Order;

use Cart;
use Currency;
use ViaBill\Adapter\Configuration;
use ViaBill\Adapter\Order;
use ViaBill\Config\Config;

/**
 * Class CreateOrderService
 */
class CreateOrderService
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * Order Adapter Variable Declaration.
     *
     * @var Order
     */
    private $orderAdapter;

    /**
     * Configuration Variable Declaration.
     *
     * @var Configuration
     */
    private $configuration;

    /**
     * CreateOrderService constructor.
     *
     * @param \ViaBill $module
     * @param Order $order
     * @param Configuration $configuration
     */
    public function __construct(\ViaBill $module, Order $order, Configuration $configuration)
    {
        $this->module = $module;
        $this->orderAdapter = $order;
        $this->configuration = $configuration;
    }

    /**
     * Creates New Order.
     *
     * @param Cart $cart
     * @param Currency $currency
     *
     * @return \Order
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createOrder(Cart $cart, Currency $currency)
    {
        $customer = new \Customer($cart->id_customer);
        $totalAmount = (float) $cart->getOrderTotal();

        $idOrderState = $this->configuration->get(Config::PAYMENT_PENDING);

        $this->module->validateOrder(
            $cart->id,
            $idOrderState,
            $totalAmount,
            $this->module->name,
            null,
            [],
            $currency->id,
            false,
            $customer->secure_key
        );

        $idOrder = $this->orderAdapter->getIdByCartId($cart->id);

        return new \Order($idOrder);
    }
}
