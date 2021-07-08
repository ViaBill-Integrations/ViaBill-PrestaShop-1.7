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

use ViaBill\Config\Config;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 *
 * @param Viabill $module
 * @return bool
 */
function upgrade_module_1_1_5($module)
{
    $status = $module->registerHook('actionOrderStatusUpdate') &&
        $module->registerHook('actionOrderStatusPostUpdate');

    $orderStateToUpdate = [
        Configuration::get(Config::PAYMENT_ACCEPTED),
        Configuration::get(Config::PAYMENT_COMPLETED)
    ];

    $db = Db::getInstance();

    foreach ($orderStateToUpdate as $orderState) {
        $query = 'UPDATE' . _DB_PREFIX_ . '`order_state` SET `logable`= 1,`paid`= 1 WHERE `id_order_state` ='
            . (int) $orderState;

        if (!$db->execute($query)) {
            $status = false;
        }
    }

    return $status;
}
