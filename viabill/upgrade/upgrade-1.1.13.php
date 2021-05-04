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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 *
 * @param Viabill $module
 * @return bool
 */
function upgrade_module_1_1_13($module)
{
    $sql = '
        CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'viabill_pending_order_cart` (
            `id_viabill_pending_order_cart`  INT(64)  NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `order_id` INT(64) NOT NULL,
            `cart_id` INT(64) NOT NULL
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;
    ';

    if (Db::getInstance()->execute($sql) == false) {
        return false;
    }

    return true;
}
