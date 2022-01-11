<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
*
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Viabill */

namespace ViaBill\Service\Cart;

use Order;
use ViaBillPendingOrderCart;

class OrderCartAssociationService
{
    private $cartDuplication;

    public function __construct(CartDuplicationService $cartDuplication)
    {
        $this->cartDuplication = $cartDuplication;
    }

    /**
     * @param Order $order
     *
     * @return bool
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
     */
    public function createPendingCart(Order $order)
    {
        // globally restores the cart.
        $newCartId = $this->cartDuplication->restoreCart($order->id_cart);

        $pendingOrderCart = new ViaBillPendingOrderCart();
        $pendingOrderCart->cart_id = $newCartId;
        $pendingOrderCart->order_id = $order->id;

        return $pendingOrderCart->add();
    }
}
