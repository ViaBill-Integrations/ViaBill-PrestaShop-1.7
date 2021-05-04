<?php
/**
 * NOTICE OF LICENSE
 *
 * @author    Written for or by ViaBill
* @copyright Copyright (c) Viabill
* @license   Addons PrestaShop license limitation
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Viabill */

/**
 * Holds data for duplicated cart -> order id from which cart was duplicated.
 */
class ViaBillPendingOrderCart extends ObjectModel
{
    /**
     * @var int
     */
    public $order_id;

    /**
     * @var int
     */
    public $cart_id;

    /**
     * @var array
     */
    public static $definition = [
        'table' => 'viabill_pending_order_cart',
        'primary' => 'id_viabill_pending_order_cart',
        'fields' => [
            'order_id' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'cart_id' => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
        ],
    ];
}
