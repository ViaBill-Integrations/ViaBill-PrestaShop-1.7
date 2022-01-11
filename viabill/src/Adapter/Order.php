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

namespace ViaBill\Adapter;

/**
 * Class Order
 */
class Order
{
    /**
     * Gets Order ID By Cart ID.
     *
     * @param $idCart
     *
     * @return int
     */
    public function getIdByCartId($idCart)
    {
        return \Order::getIdByCartId($idCart);
    }
}
