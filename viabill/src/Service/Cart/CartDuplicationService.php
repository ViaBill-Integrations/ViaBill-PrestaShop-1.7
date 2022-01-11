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

use Cart;
use Context;

class CartDuplicationService
{
    /**
     * @param int $cartId
     *
     * @return int
     *
     * @throws \PrestaShopDatabaseException
     */
    public function restoreCart($cartId)
    {
        $context = Context::getContext();
        $cart = new Cart($cartId);
        $duplication = $cart->duplicate();
        if ($duplication['success']) {
            /** @var Cart $duplicatedCart */
            $duplicatedCart = $duplication['cart'];

            $context->cookie->id_cart = $duplicatedCart->id;
            $context->cart = $duplicatedCart;
            $context->cookie->write();

            return $duplicatedCart->id;
        }

        return 0;
    }
}
