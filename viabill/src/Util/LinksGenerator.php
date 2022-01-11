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

namespace ViaBill\Util;

use Link;
use Order;

/**
 * Class LinksGenerator
 */
class LinksGenerator
{
    /**
     * Module Main Class Variable Declaration.
     *
     * @var \ViaBill
     */
    private $module;

    /**
     * LinksGenerator constructor.
     *
     * @param \ViaBill $module
     */
    public function __construct(\ViaBill $module)
    {
        $this->module = $module;
    }

    /**
     * Gets Order Confirmation Link.
     *
     * @param Link $link
     * @param Order $order
     * @param array $params
     *
     * @return string
     */
    public function getOrderConfirmationLink(Link $link, Order $order, array $params = [])
    {
        return $link->getPageLink(
            'order-confirmation',
            null,
            null,
            array_merge(
                [
                    'id_cart' => $order->id_cart,
                    'id_module' => $this->module->id,
                    'key' => $order->secure_key,
                ],
                $params
            )
        );
    }

    /**
     * Gets Order Cancel Link.
     *
     * @param Link $link
     * @param Order $order
     *
     * @return string
     */
    public function getCancelLink(Link $link, Order $order)
    {
        $params = [
            'id_cart' => $order->id_cart,
            'id_module' => $this->module->id,
            'key' => $order->secure_key,
        ];

        return $link->getModuleLink($this->module->name, 'cancel', $params);
    }
}
