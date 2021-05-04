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

namespace ViaBill\Service\Validator\Payment;

use Cart;
use ViaBill\Object\Validator\ValidationResponse;

/**
 * Class CartValidator
 *
 * @package ViaBill\Service\Validator\Payment
 */
class CartValidator
{
    /**
     * Validates Cart.
     *
     * @param Cart $cart
     * @param \Customer $customer
     *
     * @return ValidationResponse
     */
    public function validate(Cart $cart, \Customer $customer)
    {
        if (0 == $cart->id_customer ||
            0 == $cart->id_address_invoice ||
            $customer->id != $cart->id_customer
        ) {
            return new ValidationResponse(false);
        }

        return new ValidationResponse(true);
    }
}
